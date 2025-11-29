// watchSFTP.js
// Detect new *_BACKGROUND.jpg on SFTP, OCR bottom bar, send JSON to backend.

const SftpClient = require("ssh2-sftp-client");
const fs = require("fs");
const path = require("path");
const axios = require("axios");
const Tesseract = require("tesseract.js");
require("dotenv").config();

// --- Optional sharp (for newer Node). Falls back to Jimp automatically. ---
let sharp = null;
try {
  sharp = require("sharp");
} catch {
  /* sharp not available, will use Jimp */
}
let Jimp; // lazy-loaded when needed

const sftp = new SftpClient();

const config = {
  host: process.env.SFTP_HOST,
  port: Number(process.env.SFTP_PORT || 22),
  username: process.env.SFTP_USER,
  password: process.env.SFTP_PASS,
};

const remoteDir = process.env.REMOTE_DIR || "/";
const backendUrl = process.env.BACKEND_URL || "";
const pollMs = Number(process.env.POLL_MS || 5000);

const localDir = path.join(__dirname, "downloads");
if (!fs.existsSync(localDir)) fs.mkdirSync(localDir, { recursive: true });

// Keep track of files we've already sent
const processed = new Set();

// ---------------------- SFTP helpers ----------------------
async function connectSftp(retryMs = 3000) {
  while (true) {
    try {
      console.log(`[SFTP] Connecting ${config.host}:${config.port} ...`);
      await sftp.connect(config);
      console.log("[SFTP] Connected.");
      return;
    } catch (e) {
      console.error(
        "[SFTP] Connect failed:",
        e.message,
        `Retry in ${retryMs}ms`
      );
      await new Promise((r) => setTimeout(r, retryMs));
    }
  }
}

async function listBackgroundFiles() {
  try {
    const items = await sftp.list(remoteDir);
    return items
      .filter((f) => f.type === "-" && /_BACKGROUND\.jpg$/i.test(f.name))
      .map((f) => f.name)
      .sort();
  } catch (e) {
    console.error("[SFTP] list error:", e.message);
    return [];
  }
}

async function download(remoteName) {
  const remotePath = path.posix.join(remoteDir, remoteName);
  const localPath = path.join(localDir, remoteName);
  await sftp.fastGet(remotePath, localPath);
  return localPath;
}

// ---------------------- OCR Preprocess ----------------------
async function ensureJimp() {
  if (!Jimp) {
    Jimp = (await import("jimp")).default;
  }
}

// Crop bottom bar, upscale, binarize, invert, denoise
async function cropAndPrepBottomBar(imagePath) {
  // If sharp is present (and compatible), use it; else Jimp.
  if (sharp) {
    const img = sharp(imagePath);
    const meta = await img.metadata();
    // Adjust 0.25 to match your overlay height (0.22–0.30 common)
    const cropH = Math.round(meta.height * 0.25);
    const top = meta.height - cropH;

    const buf = await img
      .extract({ left: 0, top, width: meta.width, height: cropH })
      .resize({ width: meta.width * 3, height: cropH * 3, kernel: "nearest" }) // enlarge
      .greyscale()
      .normalize()
      .linear(2.0, -30) // very strong contrast
      .modulate({ brightness: 1.2, contrast: 1.5 })
      .negate() // invert (white → black text)
      .threshold(150) // stricter binarization
      .toBuffer();
    return buf;
  } else {
    await ensureJimp();
    const img = await Jimp.read(imagePath);
    const h = img.getHeight(),
      w = img.getWidth();
    const cropH = Math.round(h * 0.25); // tune if your bar is taller/shorter
    const y = h - cropH;

    img
      .crop(0, y, w, cropH)
      .resize({
        w: Math.round(w * 2.5),
        h: Math.round(cropH * 2.5),
        mode: Jimp.RESIZE_NEAREST_NEIGHBOR,
      })
      .greyscale()
      .contrast(0.6) // 0..1
      .invert();

    // simple binarization at 170
    img.scan(0, 0, img.bitmap.width, img.bitmap.height, function (x, y, idx) {
      const v = this.bitmap.data[idx]; // R=G=B in greyscale
      const t = v > 170 ? 255 : 0;
      this.bitmap.data[idx] = t;
      this.bitmap.data[idx + 1] = t;
      this.bitmap.data[idx + 2] = t;
    });

    return await img.getBufferAsync(Jimp.MIME_PNG);
  }
}

async function ocrBottomBar(imagePath) {
  const prepped = await cropAndPrepBottomBar(imagePath);

  const { data } = await Tesseract.recognize(prepped, "eng", {
    tessedit_pageseg_mode: 6,
    tessedit_ocr_engine_mode: 1,
    user_defined_dpi: "300",
    preserve_interword_spaces: "1",
    tessedit_char_whitelist:
      "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789:+%/.-,()% ",
  });

  let text = (data.text || "").replace(/\s+/g, " ").trim();

  // fix common overlay OCR mistakes
  text = text.replace(/(\d{1,3})X\b/g, "$1%"); // "96X" -> "96%"
  text = text.replace(/\bO(?=\d)/g, "0");
  text = text.replace(/\bI(?=\d)/g, "1");
  text = text.replace(/\bS(?=\d)/g, "5");

  return text;
}

// ---------------------- Parse text -> JSON ----------------------
const fieldsInOrder = [
  "Camera Info.",
  "Device No.",
  "Capture Time",
  "Plate No.",
  "Vehicle Color",
  "Vehicle Type",
  "Vehicle Brand",
  "Moving Direction",
  "Validity",
  "Country/Region",
  "Plate Color",
  "Plate Size",
  "Plate Type",
  "Province",
  "Category",
  "Camera No.",
];

const keyMap = {
  "Camera Info.": "CameraInfo",
  "Device No.": "DeviceNo",
  "Capture Time": "CaptureTime",
  "Plate No.": "PlateNo",
  "Vehicle Color": "VehicleColor",
  "Vehicle Type": "VehicleType",
  "Vehicle Brand": "VehicleBrand",
  "Moving Direction": "Direction",
  Validity: "Validity",
  "Country/Region": "CountryRegion",
  "Plate Color": "PlateColor",
  "Plate Size": "PlateSize",
  "Plate Type": "PlateType",
  Province: "Province",
  Category: "Category",
  "Camera No.": "CameraNo",
};

function escapeRegex(s) {
  return s.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
}

function parseBottomBarText(text) {
  const escaped = fieldsInOrder.map((f) => escapeRegex(f));
  const lookahead = `(?=${escaped.map((e) => `${e}\\s*:?`).join("|")}|$)`;

  const result = {};
  for (let i = 0; i < fieldsInOrder.length; i++) {
    const f = fieldsInOrder[i];
    const pattern = new RegExp(
      `${escapeRegex(f)}\\s*:?\\s*(.*?)\\s*${lookahead}`,
      "i"
    );
    const m = text.match(pattern);
    if (m && m[1]) {
      const key = keyMap[f] || f.replace(/\s+/g, "");
      result[key] = m[1].trim();
    }
  }

  // Normalize time to ISO-like "YYYY-MM-DD HH:mm:ss" as CaptureTimeISO
  if (result.CaptureTime) {
    const mt = result.CaptureTime.match(
      /(\d{2})-(\d{2})-(\d{4})\s+(\d{2}:\d{2}:\d{2})/
    );
    if (mt) result.CaptureTimeISO = `${mt[3]}-${mt[1]}-${mt[2]} ${mt[4]}`;
  }

  // Clean spaces
  Object.keys(result).forEach(
    (k) => (result[k] = result[k].replace(/\s+/g, " ").trim())
  );

  return result;
}

// ---------------------- Send to backend ----------------------
async function sendToBackend(payload) {
  if (!backendUrl) {
    console.warn("[SEND] BACKEND_URL missing; skipping POST.");
    return;
  }
  try {
    await axios.post(backendUrl, payload, { timeout: 15000 });
    console.log("[SEND] Posted to backend.");
  } catch (e) {
    console.error("[SEND] POST failed:", e.message);
  }
}

// ---------------------- Main handling ----------------------
async function handleNewFile(name) {
  try {
    console.log(`\n[DETECT] New file: ${name}`);
    const localPath = await download(name);
    console.log("[DL] Saved:", localPath);

    const text = await ocrBottomBar(localPath);
    console.log("[OCR]", text);

    const parsed = parseBottomBarText(text);

    const payload = {
      filename: name,
      parsed,
      raw: text,
      receivedAt: new Date().toISOString(),
    };

    console.log("[JSON]", payload);
    await sendToBackend(payload);

    processed.add(name);
  } catch (e) {
    console.error("[PROCESS] Error:", e);
  }
}

async function watch() {
  await connectSftp();

  // Initial snapshot: treat existing *_BACKGROUND.jpg as already processed
  try {
    const initial = await listBackgroundFiles();
    initial.forEach((f) => processed.add(f));
    console.log(`[INIT] Existing files: ${initial.length}`);
  } catch (e) {
    console.error("[INIT] list error:", e.message);
  }

  console.log(`[WATCH] Polling every ${pollMs} ms...`);
  setInterval(async () => {
    try {
      const files = await listBackgroundFiles();
      const newOnes = files.filter((f) => !processed.has(f));
      for (const f of newOnes) {
        await handleNewFile(f);
      }
    } catch (e) {
      console.error("[WATCH] Error:", e.message);
      // If connection dropped, try reconnecting
      try {
        if (!sftp.sftp) await connectSftp();
      } catch (e2) {
        console.error("[WATCH] Reconnect failed:", e2.message);
      }
    }
  }, pollMs);
}

// Graceful exit
process.on("SIGINT", async () => {
  console.log("\n[EXIT] Closing SFTP...");
  try {
    await sftp.end();
  } catch {}
  process.exit(0);
});

watch().catch((e) => {
  console.error("[FATAL]", e);
  process.exit(1);
});
