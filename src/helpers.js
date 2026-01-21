// helpers.js (MERGED VERSION)
// - Keeps all your existing logic
// - Changes logger() to write logs in: logs/YYYY-MM-DD/<ServiceName>.log
// - Adds sanitizeServiceName() + getLogDayFolder()
// - Updates spawnWrapper + spawnPhpCgiWorker to use cleaner log messages (no double prefix)
// - Updates stopServices() shutdown file to date folder: logs/YYYY-MM-DD/XtremeGuardParking_SHUTDOWN.log

const simpleGit = require("simple-git");
const fs = require("fs");
const path = require("path");
const { spawn, spawnSync, execSync } = require("child_process");
const os = require("os");
const { app, Notification, dialog, Menu } = require("electron");
const unzipper = require("unzipper");

const si = require("systeminformation");
const crypto = require("crypto");

const isDev = !app.isPackaged;

let appDir;
if (isDev) {
  appDir = path.join(__dirname);
} else {
  appDir = process.resourcesPath; // where extraResources are placed
}

const networkInterfaces = os.networkInterfaces();

let ipv4Address = "localhost";

Object.keys(networkInterfaces).forEach((interfaceName) => {
  networkInterfaces[interfaceName].forEach((networkInterface) => {
    // Only consider IPv4 addresses, ignore internal and loopback addresses
    if (networkInterface.family === "IPv4" && !networkInterface.internal) {
      ipv4Address = networkInterface.address;
    }
  });
});

// -------------------- NEW HELPERS (FOR DATE FOLDER LOGS) --------------------
function sanitizeServiceName(name) {
  return String(name || "Service")
    .replace(/^\s*\[/, "") // remove leading [
    .replace(/\]\s*$/, "") // remove trailing ]
    .trim()
    .replace(/\s+/g, "") // remove spaces
    .replace(/[^a-zA-Z0-9-_]/g, ""); // safe filename chars
}

function getLogDayFolder() {
  // YYYY-MM-DD (UTC). If you want Dubai date specifically, tell me; we can use Intl with Asia/Dubai.
  return new Date().toISOString().slice(0, 10);
}

// -------------------- UPDATED LOGGER (DATE FOLDER + SERVICE FILE) --------------------
function logger(processType, message) {
  const now = new Date();
  const timestamp = now.toISOString().replace("T", " ").split(".")[0]; // YYYY-MM-DD HH:mm:ss
  const fullMessage = `[${timestamp}] ${message}\n`;

  // logs/YYYY-MM-DD/<ServiceName>.log
  const dayFolder = getLogDayFolder();
  const logDir = path.join(appDir, "logs", dayFolder);

  const serviceName = sanitizeServiceName(processType);
  const logFile = path.join(logDir, `${serviceName}.log`);

  if (!fs.existsSync(logDir)) {
    fs.mkdirSync(logDir, { recursive: true });
  }

  fs.appendFile(logFile, fullMessage, (err) => {
    if (err) {
      console.error("❌ Failed to write log file:", err);
    }
  });
}

// Flexible spawn wrapper
function spawnWrapper(processType, command, argsOrOptions, maybeOptions) {
  let args = [];
  let options = {};

  if (Array.isArray(argsOrOptions)) {
    args = argsOrOptions;
    options = maybeOptions || {};
  } else {
    options = argsOrOptions || {};
  }

  const child = spawn(command, args, options);

  // ✅ cleaner logging (no double prefix)
  if (child.stdout) {
    child.stdout.on("data", (data) => {
      const msg = data.toString().trim();
      if (msg) logger(processType, msg);
    });
  }

  if (child.stderr) {
    child.stderr.on("data", (data) => {
      const msg = data.toString().trim();
      if (msg) logger(processType, `ERR: ${msg}`);
    });
  }

  child.on("close", (code) => {
    logger(
      processType,
      `Exited with code ${code} for ${JSON.stringify(argsOrOptions)}`,
    );
  });

  child.on("error", (err) => {
    logger(processType, `ERROR: ${err.message}`);
  });

  return child.pid;
}

function spawnPhpCgiWorker(phpCGi, port) {
  const args = ["-b", `127.0.0.1:${port}`];
  const options = { cwd: appDir };

  function start() {
    const child = spawn(phpCGi, args, options);

    const serviceName = `PHP-CGI-${port}`;

    if (child.stdout) {
      child.stdout.on("data", (data) => {
        const msg = data.toString().trim();
        if (msg) logger(serviceName, msg);
      });
    }

    if (child.stderr) {
      child.stderr.on("data", (data) => {
        const msg = data.toString().trim();
        if (msg) logger(serviceName, `ERR: ${msg}`);
      });
    }

    child.on("close", (code) => {
      logger(serviceName, `Exited with code ${code}. Restarting in 2s...`);
      setTimeout(start, 2000); // auto-restart after 2 seconds
    });

    child.on("error", (err) => {
      logger(serviceName, `ERROR: ${err.message}`);
    });

    return child.pid;
  }

  return start();
}

const timezoneOptions = {
  year: "numeric",
  month: "2-digit",
  day: "2-digit",
  hour: "2-digit",
  minute: "2-digit",
  second: "2-digit",
  hour12: false, // Use 24-hour format
  timeZone: "Asia/Dubai",
};

function getFormattedDate() {
  const [newDate, newTime] = new Intl.DateTimeFormat("en-US", timezoneOptions)
    .format(new Date())
    .split(",");
  const [m, d, y] = newDate.split("/");

  return {
    date: `${d.padStart(2, 0)}-${m.padStart(2, 0)}-${y}`,
    time: newTime,
  };
}

function notify(title = "", body = "", icon = "favicon", onClick = null) {
  const notification = new Notification({
    title,
    body,
    icon: path.join(appDir, "www", icon),
  });

  if (onClick && typeof onClick === "function") {
    notification.on("click", onClick);
  }
  notification.show();
}

/**
 * Checks if VS Redistributable is already installed
 * @param {string} displayName - Part of the name to check in installed programs
 * @returns {boolean}
 */
function isVSRedistInstalled(displayName = "Microsoft Visual C++") {
  const psScript = `
    Get-ItemProperty HKLM:\\Software\\Microsoft\\Windows\\CurrentVersion\\Uninstall\\*,
                      HKLM:\\Software\\WOW6432Node\\Microsoft\\Windows\\CurrentVersion\\Uninstall\\* |
    Where-Object { $_.DisplayName -like "*${displayName}*" } |
    Select-Object -ExpandProperty DisplayName
  `;

  const result = spawnSync("powershell.exe", ["-Command", psScript], {
    encoding: "utf8",
  });

  return result.stdout && result.stdout.trim().length > 0;
}

function runInstaller(installerPath) {
  return new Promise((resolve, reject) => {
    if (isVSRedistInstalled()) {
      logger("VS_REDIST", "✅ VS Redistributable already installed.");
      return resolve("Already installed");
    }

    const installer = spawn(installerPath, ["/quiet", "/norestart"]);

    installer.stdout.on("data", (data) => {
      logger("VS_REDIST", data.toString().trim());
    });

    installer.stderr.on("data", (data) => {
      logger("VS_REDIST", `ERR: ${data.toString().trim()}`);
    });

    installer.on("close", (code) => {
      if (code === 0) {
        logger("VS_REDIST", "Installed successfully");
        resolve("Installed successfully");
      } else if (code === 1638) {
        logger("VS_REDIST", "Already installed (code 1638)");
        resolve("Already installed");
      } else {
        logger("VS_REDIST", `❌ Installation failed with code ${code}`);
        reject(new Error(`Installation failed with code ${code}`));
      }
    });
  });
}

async function openUpdaterWindow() {
  const result = await dialog.showOpenDialog({
    title: "Select Update ZIP File",
    filters: [{ name: "ZIP Files", extensions: ["zip"] }],
    properties: ["openFile"],
  });

  logger("Updater", `openUpdaterWindow: ${JSON.stringify(result)}`);

  if (!result.canceled && result.filePaths.length > 0) {
    const zipPath = result.filePaths[0];
    await applyUpdate(zipPath);
  }
}

async function applyUpdate(zipPath) {
  try {
    const tempDir = path.join(appDir, "XtremeGuardParkingUpdate");

    if (fs.existsSync(tempDir)) {
      fs.rmSync(tempDir, { recursive: true, force: true });
    }
    fs.mkdirSync(tempDir, { recursive: true });

    await fs
      .createReadStream(zipPath)
      .pipe(unzipper.Extract({ path: tempDir }))
      .promise();

    copyFolderRecursiveSync(tempDir, appDir);

    fs.rmSync(tempDir, { recursive: true, force: true });

    dialog
      .showMessageBox({
        type: "info",
        title: "Update Applied",
        message: "Update applied successfully. The app will now restart.",
      })
      .then(() => {
        app.relaunch();
        app.exit();
      });
  } catch (err) {
    logger("Updater", `Failed to apply update: ${err}`);
  }
}

function copyFolderRecursiveSync(source, target) {
  if (!fs.existsSync(source)) return;
  const files = fs.readdirSync(source);
  files.forEach((file) => {
    const srcPath = path.join(source, file);
    const destPath = path.join(target, file);
    if (fs.lstatSync(srcPath).isDirectory()) {
      if (!fs.existsSync(destPath)) fs.mkdirSync(destPath);
      copyFolderRecursiveSync(srcPath, destPath);
    } else {
      fs.copyFileSync(srcPath, destPath);
    }
  });
}

function setMenu() {
  const template = [
    {
      label: "File",
      submenu: [
        {
          label: "Update App",
          click: () => openUpdaterWindow(),
        },
        { role: "quit" },
      ],
    },
  ];

  const menu = Menu.buildFromTemplate(template);
  Menu.setApplicationMenu(menu);
}

function stopServices(pid) {
  return new Promise((resolve) => {
    const dayFolder = getLogDayFolder();
    const shutdownDir = path.join(appDir, "logs", dayFolder);
    const shutdownFile = path.join(
      shutdownDir,
      "XtremeGuardParking_SHUTDOWN.log",
    );

    if (!fs.existsSync(shutdownDir)) {
      fs.mkdirSync(shutdownDir, { recursive: true });
    }

    fs.appendFileSync(shutdownFile, "Stopping services...\n");

    if (pid) {
      try {
        execSync(`taskkill /PID ${pid} /T /F`);
        fs.appendFileSync(shutdownFile, `✅ ${pid} stopped\n`);
      } catch (err) {
        fs.appendFileSync(shutdownFile, `ℹ️ Failed to stop ${pid}\n`);
      }
    }

    setTimeout(resolve, 2000);
  });
}

async function generateHardwareFingerprint() {
  const [cpu, baseboard, disks, network, osInfo] = await Promise.all([
    si.cpu(),
    si.baseboard(),
    si.diskLayout(),
    si.networkInterfaces(),
    si.osInfo(),
  ]);

  const primaryDisk = disks.find((d) => d.serial) || {};

  const macs = network
    .filter((n) => !n.internal && n.mac)
    .map((n) => n.mac)
    .join(",");

  const raw = [
    cpu.brand,
    cpu.physicalCores,
    baseboard.serial || "NO_MB",
    primaryDisk.serial || "NO_DISK",
    osInfo.hostname,
    macs,
  ].join("|");

  return crypto.createHash("sha256").update(raw).digest("hex");
}

async function getCachedMachineId() {
  const filePath = path.join(appDir, "machine.id");

  if (fs.existsSync(filePath)) {
    return fs.readFileSync(filePath, "utf8");
  }

  const id = await generateHardwareFingerprint();
  fs.writeFileSync(filePath, id, "utf8");
  return id;
}

// ---------------- CLOCK TAMPERING PROTECTION ----------------
const CLOCK_FILE = path.join(appDir, "clock.json");

function readClock() {
  if (!fs.existsSync(CLOCK_FILE)) return null;
  try {
    return JSON.parse(fs.readFileSync(CLOCK_FILE, "utf8"));
  } catch {
    return null;
  }
}

function writeClock(ts) {
  fs.writeFileSync(CLOCK_FILE, JSON.stringify({ last: ts }));
}

function isClockTampered() {
  const data = readClock();
  if (!data || !data.last) {
    writeClock(Date.now());
    return false;
  }

  const now = Date.now();
  const ALLOWED_DRIFT = 5 * 60 * 1000; // 5 minutes

  if (now + ALLOWED_DRIFT < data.last) {
    return true;
  }

  writeClock(now);
  return false;
}

function cleanupOldLogs(daysToKeep = 2) {
  try {
    const logsRoot = path.join(appDir, "logs");
    if (!fs.existsSync(logsRoot)) return;

    const now = Date.now();
    const maxAgeMs = daysToKeep * 24 * 60 * 60 * 1000;

    const entries = fs.readdirSync(logsRoot, { withFileTypes: true });

    entries.forEach((entry) => {
      if (!entry.isDirectory()) return;

      // Expect folder name: YYYY-MM-DD
      const folderName = entry.name;
      if (!/^\d{4}-\d{2}-\d{2}$/.test(folderName)) return;

      const folderPath = path.join(logsRoot, folderName);
      const folderDate = new Date(folderName + "T00:00:00Z").getTime();

      if (now - folderDate > maxAgeMs) {
        fs.rmSync(folderPath, { recursive: true, force: true });
        logger("LOG-CLEANUP", `Deleted old log folder: ${folderName}`);
      }
    });
  } catch (err) {
    logger("LOG-CLEANUP", `ERROR: ${err.message}`);
  }
}

module.exports = {
  logger,
  runInstaller,
  stopServices,
  spawnWrapper,
  spawnPhpCgiWorker,
  ipv4Address,
  setMenu,
  getCachedMachineId,
  isClockTampered,
  cleanupOldLogs, // ✅ ADD THIS
};
