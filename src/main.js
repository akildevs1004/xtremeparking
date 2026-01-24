/**
 * main.js — FULL CODE (Log-only observability)
 *
 * What you asked:
 * 1) NO camera logs displayed in UI (service-status.html). Only written to logs folder.
 * 2) Logs are grouped by date folder: logs/YYYY-MM-DD/
 * 3) Camera log files are renamed:
 *    - CAM1 -> camera_files_organizer.log
 *    - CAM2 -> camera_live_streaming.log
 *    - CAM3 -> camera_vehicle_event_images.log
 * 4) Camera require modules start ONLY AFTER artisan (port 8000) is listening.
 *
 * Notes:
 * - Your existing svcUpdate/svcMeta etc are kept for showing port-based services status
 *   (mosquitto/nginx/php/laravel). Camera logs are file-only.
 */

const { app, BrowserWindow, screen, ipcMain, dialog } = require("electron");
const fs = require("fs");
const path = require("path");
const { execSync } = require("child_process");
const { cleanupOldLogs } = require("./helpers");

const {
  logger,
  spawnWrapper,
  spawnPhpCgiWorker,
  runInstaller,
  ipv4Address,
  setMenu,
  stopServices,
  getCachedMachineId,
  isClockTampered,
} = require("./helpers");

app.setName("XtremeGuard Parking");
app.setAppUserModelId("XtremeGuardParking");

// -------------------- SINGLE INSTANCE --------------------
const gotLock = app.requestSingleInstanceLock();
if (!gotLock) {
  app.quit();
} else {
  app.on("second-instance", () => {
    if (mainWindow && !mainWindow.isDestroyed()) {
      if (mainWindow.isMinimized()) mainWindow.restore();
      mainWindow.focus();
    }
  });
}

let mainWindow = null;
let isQuitting = false;

const isDev = !app.isPackaged;
const appDir = isDev ? process.cwd() : process.resourcesPath;

const srcDirectory = path.join(appDir, "www");
const statusHtmlPath = path.join(appDir, "service-status.html");

const nginxPath = path.join(appDir, "nginx.exe");
const phpPath = path.join(srcDirectory, "php");
const phpPathCli = path.join(phpPath, "php.exe");
const phpCGi = path.join(phpPath, "php-cgi.exe");

// -------------------- PATH HELPERS --------------------
function firstExists(list) {
  return list.find((p) => p && fs.existsSync(p)) || null;
}

// Mosquitto paths (prefer installed, fallback to bundled)
const PROGRAM_FILES =
  process.env.ProgramW6432 || process.env.ProgramFiles || "C:\\Program Files";
const PROGRAM_FILES_X86 =
  process.env["ProgramFiles(x86)"] || "C:\\Program Files (x86)";

const mosquittoPath = firstExists([
  path.join(PROGRAM_FILES, "mosquitto", "mosquitto.exe"),
  path.join(PROGRAM_FILES_X86, "mosquitto", "mosquitto.exe"),
  path.join(appDir, "mosquitto", "mosquitto.exe"),
]);

const mosquittoConf = firstExists([
  path.join(PROGRAM_FILES, "mosquitto", "mosquitto.conf"),
  path.join(PROGRAM_FILES_X86, "mosquitto", "mosquitto.conf"),
  path.join(appDir, "mosquitto", "mosquitto.conf"),
]);

// Track only what we start (so we stop only these)
const services = {
  mosquitto: { name: "Mosquitto", port: 1883, proc: null, startedByUs: false },
  artisanServe: {
    name: "ArtisanServe",
    port: 8000,
    proc: null,
    startedByUs: false,
  },
  phpCgi: { name: "PHP-CGI", port: 9000, proc: null, startedByUs: false },
  nginx: { name: "Nginx", port: 3000, proc: null, startedByUs: false },

  scheduler: { name: "Scheduler", port: null, proc: null, startedByUs: false },
  queue: { name: "Queue", port: null, proc: null, startedByUs: false },
  mqttSub: { name: "MQTT", port: null, proc: null, startedByUs: false },
  mqttQr: { name: "MQTT-QR", port: null, proc: null, startedByUs: false },
};

let MACHINE_ID = null;

// -------------------- LOGGING (DATE FOLDER + SERVICE FILES) --------------------
function getTodayFolderName() {
  const d = new Date();
  const yyyy = d.getFullYear();
  const mm = String(d.getMonth() + 1).padStart(2, "0");
  const dd = String(d.getDate()).padStart(2, "0");
  return `${yyyy}-${mm}-${dd}`;
}

function ensureLogsDir() {
  try {
    const dayFolder = path.join(appDir, "logs", getTodayFolderName());
    fs.mkdirSync(dayFolder, { recursive: true });
    return dayFolder;
  } catch {
    try {
      const base = path.join(appDir, "logs");
      fs.mkdirSync(base, { recursive: true });
      return base;
    } catch {
      return appDir;
    }
  }
}

function writeLog(fileBaseName, message) {
  try {
    const dayFolder = ensureLogsDir();
    const line = `[${new Date().toISOString()}] ${message}\n`;
    fs.appendFileSync(
      path.join(dayFolder, `${fileBaseName}.log`),
      line,
      "utf8",
    );
  } catch {}
}

/**
 * log(serviceName, message)
 * - Writes to logs/YYYY-MM-DD/<serviceName>.log
 * - Also calls your existing logger() (if it writes somewhere else, fine)
 */
function log(serviceName, message) {
  try {
    logger(serviceName, message);
  } catch {}
  writeLog(serviceName, message);
}

function sleep(ms) {
  return new Promise((resolve) => setTimeout(resolve, ms));
}

// -------------------- PORT DETECTION --------------------
function isPortListening(port) {
  try {
    const out = execSync(
      `powershell -NoProfile -Command "(Get-NetTCPConnection -LocalPort ${port} -State Listen -ErrorAction SilentlyContinue | Measure-Object).Count"`,
      { stdio: ["ignore", "pipe", "ignore"] },
    )
      .toString()
      .trim();
    const n = parseInt(out || "0", 10);
    if (n > 0) return true;
  } catch {}

  try {
    const out = execSync(`cmd /c "netstat -ano | findstr :${port}"`, {
      stdio: ["ignore", "pipe", "ignore"],
    })
      .toString()
      .toUpperCase();
    return out.includes("LISTENING");
  } catch {
    return false;
  }
}

function waitForPort(port, timeoutMs = 30000, intervalMs = 500) {
  return new Promise((resolve) => {
    const start = Date.now();
    const timer = setInterval(() => {
      if (isPortListening(port)) {
        clearInterval(timer);
        return resolve(true);
      }
      if (Date.now() - start > timeoutMs) {
        clearInterval(timer);
        return resolve(false);
      }
    }, intervalMs);
  });
}

function getPid(p) {
  if (!p) return null;
  if (typeof p === "object" && typeof p.pid === "number") return p.pid;
  return null;
}

function isPidAlive(pid) {
  if (!pid) return false;
  try {
    const out = execSync(`cmd /c "tasklist /FI \\"PID eq ${pid}\\""`, {
      stdio: ["ignore", "pipe", "ignore"],
    }).toString();
    return out.includes(String(pid));
  } catch {
    return false;
  }
}

// -------------------- STATUS UI IPC (PORT SERVICES ONLY) --------------------
function svcMeta(meta) {
  if (mainWindow && !mainWindow.isDestroyed()) {
    mainWindow.webContents.send("svc:init", { meta });
  }
}
function svcUpdate(payload) {
  if (mainWindow && !mainWindow.isDestroyed()) {
    mainWindow.webContents.send("svc:update", payload);
  }
}
function svcReady(meta) {
  if (mainWindow && !mainWindow.isDestroyed()) {
    mainWindow.webContents.send("svc:ready", { meta });
  }
}
function svcError(meta) {
  if (mainWindow && !mainWindow.isDestroyed()) {
    mainWindow.webContents.send("svc:error", { meta });
  }
}

// -------------------- GLOBAL ERROR LOGGING (FILE) --------------------
process.on("uncaughtException", (err) => {
  log("FATAL", err?.stack || String(err));
});
process.on("unhandledRejection", (reason) => {
  log("FATAL", reason?.stack || String(reason));
});

// -------------------- SOCKET INIT (NON-INVASIVE) --------------------
function initSocketNonInvasive() {
  try {
    require("./socket");
    log("SOCKET", "Socket init requested (./socket).");
  } catch (e) {
    log("SOCKET", `Socket init failed: ${e?.message || String(e)}`);
  }
}

// -------------------- CAMERA SERVICES (REQUIRE MODULES) --------------------
let cameraServicesLoaded = false;

const CAMERA_LOG_FILES = {
  cam1: "camera_files_organizer",
  cam2: "camera_live_streaming",
  cam3: "camera_vehicle_event_images",
};

function initCameraServicesNonInvasive() {
  if (cameraServicesLoaded) return;
  cameraServicesLoaded = true;

  const list = [
    {
      logFile: CAMERA_LOG_FILES.cam1,
      file: "./camera/camera_1_organize_files_by_date",
    },
    {
      logFile: CAMERA_LOG_FILES.cam2,
      file: "./camera/camera_2_start_camera_live_stream",
    },
    {
      logFile: CAMERA_LOG_FILES.cam3,
      file: "./camera/camera_3_watchCameraImages",
    },
  ];

  for (const s of list) {
    const isCamService = true; // this loop is only for cam services

    // File-only logger/heartbeat (no console/logger/svcUpdate)
    const fileOnlyLogger = (msg) => writeLog(s.logFile, msg);
    const fileOnlyBeat = (msg) => writeLog(s.logFile, `HEARTBEAT: ${msg}`);

    // Hard-mute console while requiring + starting the cam module
    // This blocks console.log/error/warn/info/debug from cam code (require-time + start-time).
    const originalConsole = {
      log: console.log,
      error: console.error,
      warn: console.warn,
      info: console.info,
      debug: console.debug,
    };

    try {
      // mute console for camera modules
      console.log = () => {};
      console.error = () => {};
      console.warn = () => {};
      console.info = () => {};
      console.debug = () => {};

      const mod = require(s.file);

      try {
        if (mod && typeof mod.start === "function") {
          mod.start({
            appDir,
            srcDirectory,
            logger: fileOnlyLogger,
            beat: fileOnlyBeat,
          });
          writeLog(s.logFile, `SERVICE STARTED via ${s.file} start().`);
        } else {
          writeLog(s.logFile, `MODULE LOADED ${s.file} (no start() export).`);
        }
      } catch (startErr) {
        writeLog(
          s.logFile,
          `START FAILED: ${startErr?.stack || startErr?.message || String(startErr)}`,
        );
      }
    } catch (e) {
      writeLog(
        s.logFile,
        `LOAD FAILED: ${e?.stack || e?.message || String(e)}`,
      );
    } finally {
      // restore console back for the rest of the app
      console.log = originalConsole.log;
      console.error = originalConsole.error;
      console.warn = originalConsole.warn;
      console.info = originalConsole.info;
      console.debug = originalConsole.debug;
    }
  }
}

/**
 * Start camera modules only after artisan is listening (port 8000).
 * This ensures camera modules can call your Laravel APIs safely.
 */
async function startCameraAfterLaravelReady() {
  try {
    writeLog(
      "camera_boot",
      "Waiting for Laravel port 8000 before starting camera modules...",
    );
    const ok = await waitForPort(8000, 60000);
    if (!ok) {
      writeLog(
        "camera_boot",
        "Laravel NOT ready on 8000 after 60s. Camera modules not started.",
      );
      return;
    }

    writeLog(
      "camera_boot",
      "Laravel ready on 8000. Starting camera modules now...",
    );
    initCameraServicesNonInvasive();
  } catch (e) {
    writeLog(
      "camera_boot",
      `startCameraAfterLaravelReady error: ${e?.stack || e?.message || String(e)}`,
    );
  }
}

// -------------------- WINDOW --------------------
function createMainWindow() {
  const { width, height } = screen.getPrimaryDisplay().workAreaSize;

  mainWindow = new BrowserWindow({
    width,
    height,
    autoHideMenuBar: true,
    webPreferences: {
      nodeIntegration: true,
      contextIsolation: false,
    },
  });

  if (!fs.existsSync(statusHtmlPath)) {
    dialog.showErrorBox(
      "Missing File",
      `service-status.html not found:\n${statusHtmlPath}`,
    );
    app.quit();
    return null;
  }

  mainWindow.loadFile(statusHtmlPath);
  mainWindow.maximize();

  mainWindow.on("closed", () => {
    mainWindow = null;
  });

  return mainWindow;
}

ipcMain.handle("open-login", async () => {
  if (mainWindow && !mainWindow.isDestroyed()) {
    await mainWindow.loadURL("http://127.0.0.1:3000/login");
  }
});

// -------------------- START SERVICES (NON-INVASIVE) --------------------
async function startMissingServicesNonInvasive() {
  svcMeta("Checking existing services…");

  const portServices = [
    { key: "mosquitto", port: 1883 },
    { key: "artisanServe", port: 8000 },
    { key: "phpCgi", port: 9000 },
    { key: "nginx", port: 3000 },
  ];

  for (const ps of portServices) {
    const s = services[ps.key];
    const up = isPortListening(ps.port);
    svcUpdate({
      name: s.name,
      status: up ? "running" : "stopped",
      port: ps.port,
      message: up
        ? "Already running. Not starting."
        : "Not running. Will start.",
    });
  }

  svcMeta("Starting missing services (5 seconds gap)…");

  // 1) Mosquitto
  if (!isPortListening(1883)) {
    svcUpdate({
      name: services.mosquitto.name,
      status: "starting",
      port: 1883,
      message: "Starting broker…",
    });
    log("Mosquitto", "Starting broker…");

    if (!mosquittoPath || !mosquittoConf) {
      svcUpdate({
        name: services.mosquitto.name,
        status: "failed",
        port: 1883,
        message: "Mosquitto exe/conf not found.",
      });
      log("Mosquitto", "FAILED: Mosquitto exe/conf not found.");
    } else {
      services.mosquitto.proc = spawnWrapper(
        "[Mosquitto]",
        mosquittoPath,
        ["-c", mosquittoConf, "-v"],
        { cwd: path.dirname(mosquittoPath), baseDir: appDir },
      );
      services.mosquitto.startedByUs = true;

      const ok = await waitForPort(1883, 25000);
      const pid = getPid(services.mosquitto.proc);

      log(
        "Mosquitto",
        ok ? `RUNNING pid=${pid} port=1883` : `FAILED pid=${pid} port=1883`,
      );
      svcUpdate({
        name: services.mosquitto.name,
        status: ok ? "running" : "failed",
        port: 1883,
        pid,
        message: ok
          ? "Listening on 1883."
          : isPidAlive(pid)
            ? "Process alive but not listening. Check conf/firewall."
            : "Process exited. Check logs.",
      });
    }
    await sleep(5000);
  }

  // 2) Artisan Serve
  if (!isPortListening(8000)) {
    svcUpdate({
      name: services.artisanServe.name,
      status: "starting",
      port: 8000,
      message: "Starting artisan serve…",
    });
    log("ArtisanServe", "Starting artisan serve…");

    services.artisanServe.proc = spawnWrapper(
      "[ArtisanServe]",
      phpPathCli,
      ["artisan", "serve", "--host=0.0.0.0", "--port=8000"],
      { cwd: srcDirectory, baseDir: appDir },
    );
    services.artisanServe.startedByUs = true;

    const ok = await waitForPort(8000, 25000);
    const pid = getPid(services.artisanServe.proc);

    log(
      "ArtisanServe",
      ok ? `RUNNING pid=${pid} port=8000` : `FAILED pid=${pid} port=8000`,
    );
    svcUpdate({
      name: services.artisanServe.name,
      status: ok ? "running" : "failed",
      port: 8000,
      pid,
      message: ok
        ? "Listening on 8000."
        : isPidAlive(pid)
          ? "Process alive but not listening. Check Laravel boot errors."
          : "Process exited. Check logs.",
    });

    await sleep(5000);
  }

  // 3) PHP-CGI
  if (!isPortListening(9000)) {
    svcUpdate({
      name: services.phpCgi.name,
      status: "starting",
      port: 9000,
      message: "Starting php-cgi worker…",
    });
    log("PHP-CGI", "Starting php-cgi worker…");

    services.phpCgi.proc = spawnPhpCgiWorker(phpCGi, 9000, {
      cwd: srcDirectory,
      baseDir: appDir,
    });
    services.phpCgi.startedByUs = true;

    const ok = await waitForPort(9000, 25000);
    const pid = getPid(services.phpCgi.proc);

    log(
      "PHP-CGI",
      ok ? `RUNNING pid=${pid} port=9000` : `FAILED pid=${pid} port=9000`,
    );
    svcUpdate({
      name: services.phpCgi.name,
      status: ok ? "running" : "failed",
      port: 9000,
      pid,
      message: ok
        ? "Listening on 9000."
        : isPidAlive(pid)
          ? "Process alive but not listening. Check php-cgi."
          : "Process exited. Check logs.",
    });

    await sleep(5000);
  }

  // 4) Nginx
  if (!isPortListening(3000)) {
    svcUpdate({
      name: services.nginx.name,
      status: "starting",
      port: 3000,
      message: "Starting nginx…",
    });
    log("Nginx", "Starting nginx…");

    services.nginx.proc = spawnWrapper(
      "[Nginx]",
      nginxPath,
      ["-p", appDir, "-c", "conf/nginx.conf"],
      { cwd: appDir, baseDir: appDir },
    );
    services.nginx.startedByUs = true;

    const ok = await waitForPort(3000, 30000);
    const pid = getPid(services.nginx.proc);

    log(
      "Nginx",
      ok ? `RUNNING pid=${pid} port=3000` : `FAILED pid=${pid} port=3000`,
    );
    svcUpdate({
      name: services.nginx.name,
      status: ok ? "running" : "failed",
      port: 3000,
      pid,
      message: ok
        ? "Listening on 3000."
        : isPidAlive(pid)
          ? "Process alive but not listening. Check nginx.conf."
          : "Process exited. Check nginx logs inside daily folder.",
    });

    await sleep(5000);
  }

  // 5) Scheduler
  svcUpdate({
    name: services.scheduler.name,
    status: "starting",
    message: "Starting schedule:work…",
  });
  log("Scheduler", "Starting schedule:work…");

  services.scheduler.proc = spawnWrapper(
    "[Scheduler]",
    phpPathCli,
    ["artisan", "schedule:work"],
    { cwd: srcDirectory, baseDir: appDir },
  );
  services.scheduler.startedByUs = true;

  log("Scheduler", `RUNNING pid=${getPid(services.scheduler.proc)}`);
  svcUpdate({
    name: services.scheduler.name,
    status: "running",
    pid: getPid(services.scheduler.proc),
    message: "Started.",
  });
  await sleep(5000);

  // 6) Queue
  svcUpdate({
    name: services.queue.name,
    status: "starting",
    message: "Starting queue:work…",
  });
  log("Queue", "Starting queue:work…");

  services.queue.proc = spawnWrapper(
    "[Queue]",
    phpPathCli,
    ["artisan", "queue:work"],
    { cwd: srcDirectory, baseDir: appDir },
  );
  services.queue.startedByUs = true;

  log("Queue", `RUNNING pid=${getPid(services.queue.proc)}`);
  svcUpdate({
    name: services.queue.name,
    status: "running",
    pid: getPid(services.queue.proc),
    message: "Started.",
  });
  await sleep(5000);

  // 7) MQTT subscribe
  svcUpdate({
    name: services.mqttSub.name,
    status: "starting",
    message: "Starting mqtt:subscribe…",
  });
  log("MQTT", "Starting mqtt:subscribe…");

  services.mqttSub.proc = spawnWrapper(
    "[MQTT]",
    phpPathCli,
    ["artisan", "mqtt:listen"],
    { cwd: srcDirectory, baseDir: appDir },
  );
  services.mqttSub.startedByUs = true;

  log("MQTT", `RUNNING pid=${getPid(services.mqttSub.proc)}`);
  svcUpdate({
    name: services.mqttSub.name,
    status: "running",
    pid: getPid(services.mqttSub.proc),
    message: "Started.",
  });
  await sleep(5000);

  // 8) MQTT QR listener
  svcUpdate({
    name: services.mqttQr.name,
    status: "starting",
    message: "Starting mqtt:qrbackgroundlistener…",
  });
  log("MQTT-QR", "Starting mqtt:qrbackgroundlistener…");

  services.mqttQr.proc = spawnWrapper(
    "[MQTT-QR]",
    phpPathCli,
    ["artisan", "mqtt:qrbackgroundlistener"],
    { cwd: srcDirectory, baseDir: appDir },
  );
  services.mqttQr.startedByUs = true;

  log("MQTT-QR", `RUNNING pid=${getPid(services.mqttQr.proc)}`);
  svcUpdate({
    name: services.mqttQr.name,
    status: "running",
    pid: getPid(services.mqttQr.proc),
    message: "Started.",
  });
  await sleep(5000);

  // Final readiness
  if (isPortListening(3000)) {
    svcReady("UI is ready on port 3000. Click Open Login (3000).");
  } else {
    svcError("UI is not ready (port 3000 not listening). Check nginx logs.");
  }

  log(
    "Application",
    `Local UI: http://127.0.0.1:3000/login | LAN UI: http://${ipv4Address}:3000/login`,
  );
}

// -------------------- REPORT WINDOW --------------------
ipcMain.handle("open-report-window", (event, url) => {
  ensureLogsDir();
  try {
    fs.appendFileSync(path.join(ensureLogsDir(), "ips.txt"), `${url}\n`);
  } catch {}

  const { width, height } = screen.getPrimaryDisplay().workAreaSize;

  const reportWindow = new BrowserWindow({
    width,
    height,
    fullscreen: true,
    webPreferences: { nodeIntegration: true, contextIsolation: false },
  });

  reportWindow.loadURL(url);
});

app.disableHardwareAcceleration();

// -------------------- APP READY --------------------
app.whenReady().then(async () => {
  // Ensure logs first, then cleanup
  ensureLogsDir();
  cleanupOldLogs(2);

  log("Application", "App starting...");

  if (isClockTampered()) {
    log("Application", "System time tamper detected. Exiting.");
    dialog.showErrorBox(
      "System Time Error",
      "System date/time appears to have been changed.\n\nPlease correct your system clock and restart the application.",
    );
    app.exit(1);
    return;
  }

  MACHINE_ID = await getCachedMachineId();
  ipcMain.handle("get-machine-id", () => MACHINE_ID);

  setMenu();
  initSocketNonInvasive();

  const win = createMainWindow();
  if (!win) return;

  setImmediate(() => {
    runInstaller(path.join(appDir, "vs_redist.exe"))
      .then(async () => {
        await startMissingServicesNonInvasive();
        await startCameraAfterLaravelReady(); // ✅ only after port 8000 is listening
      })
      .catch(async (err) => {
        log("Installer", err?.message || String(err));
        await startMissingServicesNonInvasive();
        await startCameraAfterLaravelReady();
      });
  });
});

// -------------------- WINDOW --------------------
function createMainWindow() {
  const { width, height } = screen.getPrimaryDisplay().workAreaSize;

  mainWindow = new BrowserWindow({
    width,
    height,
    autoHideMenuBar: true,
    webPreferences: {
      nodeIntegration: true,
      contextIsolation: false,
    },
  });

  if (!fs.existsSync(statusHtmlPath)) {
    dialog.showErrorBox(
      "Missing File",
      `service-status.html not found:\n${statusHtmlPath}`,
    );
    app.quit();
    return null;
  }

  mainWindow.loadFile(statusHtmlPath);
  mainWindow.maximize();

  mainWindow.on("closed", () => {
    mainWindow = null;
  });

  return mainWindow;
}

// -------------------- CLEAN QUIT --------------------
async function stopOnlyWhatWeStarted() {
  for (const key of Object.keys(services)) {
    const svc = services[key];
    if (svc.startedByUs && svc.proc) {
      try {
        await stopServices(svc.proc);
        log(svc.name, "Stopped (requested).");
      } catch (e) {
        log("STOP", `${svc.name} stop error: ${e?.message || String(e)}`);
      }
      svc.proc = null;
      svc.startedByUs = false;
    }
  }
}

app.on("before-quit", async (e) => {
  if (isQuitting) return;

  e.preventDefault();
  isQuitting = true;

  log("Application", "before-quit fired. Stopping started services...");
  try {
    fs.appendFileSync(
      path.join(ensureLogsDir(), "XtremeGuardParking_SHUTDOWN.txt"),
      `before-quit fired ${new Date().toISOString()}\n`,
    );
  } catch {}

  await stopOnlyWhatWeStarted();
  app.exit(0);
});

app.on("will-quit", () => {
  log("Application", "will-quit fired.");
  try {
    fs.appendFileSync(
      path.join(ensureLogsDir(), "XtremeGuardParking_SHUTDOWN.txt"),
      `will-quit fired ${new Date().toISOString()}\n`,
    );
  } catch {}
});
