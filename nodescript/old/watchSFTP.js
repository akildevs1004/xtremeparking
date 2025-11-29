// watchSFTP-upload.js
// Detect *_BACKGROUND.(jpg|jpeg|png) on SFTP, download, and POST via multipart/form-data.

const SftpClient = require("ssh2-sftp-client");
const axios = require("axios");
const FormData = require("form-data");
const fs = require("fs");
const path = require("path");
require("dotenv").config();

const CFG = {
  host: process.env.SFTP_HOST,
  port: Number(process.env.SFTP_PORT || 22),
  username: process.env.SFTP_USER,
  password: process.env.SFTP_PASS,
  remoteDir: process.env.REMOTE_DIR || "/",
  backendUrl: process.env.BACKEND_URL || "",
  pollMs: Math.max(1000, Number(process.env.POLL_MS || 5000)),
  settleMs: Math.max(500, Number(process.env.SETTLE_MS || 2000)),
  settleTries: Math.max(1, Number(process.env.SETTLE_TRIES || 3)),
  downloadsDir: path.join(__dirname, process.env.DOWNLOADS_DIR || "downloads"),
  deleteAfterSend:
    String(process.env.DELETE_AFTER_SEND || "false").toLowerCase() === "true", // delete local temp file
};

if (!CFG.host || !CFG.username || !CFG.password) {
  console.error("[ENV] Missing SFTP_HOST/USER/PASS");
}
if (!CFG.backendUrl) {
  console.error(
    "[ENV] BACKEND_URL not set – will only log payloads, not POST."
  );
}

// Ensure local download dir exists
if (!fs.existsSync(CFG.downloadsDir)) {
  fs.mkdirSync(CFG.downloadsDir, { recursive: true });
}

const sftp = new SftpClient();
const processed = new Set(); // filenames we already posted

const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

function isBackground(name) {
  // return /_BACKGROUND\.(jpg|jpeg|png)$/i.test(name);

  return /_(BACKGROUND|VEHICLE|PLATE)\.(jpg|jpeg|png)$/i.test(name);
}

async function connectSftp(retryMs = 3000) {
  while (true) {
    try {
      console.log(`[SFTP] Connecting ${CFG.host}:${CFG.port} ...`);
      await sftp.connect({
        host: CFG.host,
        port: CFG.port,
        username: CFG.username,
        password: CFG.password,
        readyTimeout: 15000,
      });
      console.log("[SFTP] Connected.");
      // quick sanity: list once
      await sftp.list(CFG.remoteDir);
      return;
    } catch (e) {
      console.error(
        "[SFTP] Connect failed:",
        e.message,
        `Retry in ${retryMs}ms`
      );
      await sleep(retryMs);
    }
  }
}

async function listBackgroundFiles() {
  try {
    const items = await sftp.list(CFG.remoteDir);
    return items
      .filter((f) => f.type === "-" && isBackground(f.name))
      .map((f) => ({ name: f.name, size: f.size, modifyTime: f.modifyTime }))
      .sort((a, b) => a.name.localeCompare(b.name));
  } catch (e) {
    console.error("[SFTP] list error:", e.message);
    return [];
  }
}

/** Wait until file size is unchanged across consecutive checks */
async function waitStable(remoteName) {
  const remotePath = `${CFG.remoteDir.replace(/\/+$/, "")}/${remoteName}`;
  let lastSize = -1;
  for (let i = 0; i < CFG.settleTries; i++) {
    const st = await sftp.stat(remotePath).catch(() => null);
    const size = st?.size ?? -1;
    if (size > 0 && size === lastSize) return true; // stable
    lastSize = size;
    await sleep(CFG.settleMs);
  }
  return true; // proceed anyway (some cameras keep touching mtime)
}

async function download(remoteName) {
  const remotePath = `${CFG.remoteDir.replace(/\/+$/, "")}/${remoteName}`;
  const localPath = path.join(CFG.downloadsDir, remoteName);
  await sftp.fastGet(remotePath, localPath);
  return localPath;
}

/** Send via multipart/form-data: field "file" + "meta" (JSON string) */
async function sendMultipart({ filename, localPath, remoteDir }) {
  const form = new FormData();
  form.append("file", fs.createReadStream(localPath), filename);

  const meta = {
    filename,
    remotePath: `${remoteDir.replace(/\/+$/, "")}/${filename}`,
    postedAt: new Date().toISOString(),
  };
  form.append("meta", JSON.stringify(meta));

  console.log(
    "[SEND] multipart ->",
    CFG.backendUrl || "(no BACKEND_URL set, logging only)"
  );
  if (!CFG.backendUrl) {
    console.log("[PAYLOAD META]", meta);
    return;
  }
  try {
    const res = await axios.post(CFG.backendUrl + "camera_log_listner", form, {
      headers: form.getHeaders(),
      maxContentLength: Infinity,
      maxBodyLength: Infinity,
      timeout: 30000,
    });
    console.log(`[SEND] OK (${res.status}) ${filename}`);
  } catch (e) {
    console.error("[SEND] POST failed:", e.message);
    // throw e;
  }

  console.log("[SEND] SENT ");
}

async function handleNewFile(name) {
  try {
    console.log(`\n[DETECT] New file: ${name}`);

    // Ensure fully uploaded on the server
    await waitStable(name);

    // Download to local temp
    const localPath = await download(name);
    console.log("[DL] Saved:", localPath);

    // POST to backend
    await sendMultipart({
      filename: name,
      localPath,
      remoteDir: CFG.remoteDir,
    });

    // Mark processed and cleanup
    processed.add(name);
    if (CFG.deleteAfterSend) {
      try {
        fs.unlinkSync(localPath);
      } catch {}
    }
  } catch (e) {
    console.error("[PROCESS] Error:", e);
  }
}

async function poll() {
  try {
    const files = await listBackgroundFiles();
    const newOnes = files.map((f) => f.name).filter((n) => !processed.has(n));
    for (const name of newOnes) {
      await handleNewFile(name);
    }
  } catch (e) {
    console.error("[POLL] Error:", e.message);
    try {
      if (!sftp.sftp) await connectSftp();
    } catch (e2) {
      console.error("[POLL] Reconnect failed:", e2.message);
    }
  }
}

async function main() {
  await connectSftp();

  // Ignore existing files on startup
  try {
    const existing = await listBackgroundFiles();
    existing.forEach((it) => processed.add(it.name));
    console.log(`[INIT] Existing *_BACKGROUND files: ${existing.length}`);
  } catch (e) {
    console.error("[INIT] list error:", e.message);
  }

  console.log(`[WATCH] Polling every ${CFG.pollMs} ms...`);
  setInterval(poll, CFG.pollMs);
}

process.on("SIGINT", async () => {
  console.log("\n[EXIT] Closing SFTP...");
  try {
    await sftp.end();
  } catch {}
  process.exit(0);
});

main().catch((e) => {
  console.error("[FATAL]", e);
  process.exit(1);
});
