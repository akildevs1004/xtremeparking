/**
 * main.js — FULL CODE (Auto-Restart Watchdog + UI Start/Restart + MQTT Storm Fix)
 */

const {
  app,
  BrowserWindow,
  screen,
  ipcMain,
  dialog,
  shell,
} = require("electron");
const fs = require("fs");
const path = require("path");
const { execSync } = require("child_process");

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
  cleanupOldLogs,
} = require("./helpers");

// -------------------- ELECTRON STABILITY SWITCHES --------------------
try {
  app.commandLine.appendSwitch(
    "disable-features",
    "NetworkService,NetworkServiceInProcess",
  );
  app.commandLine.appendSwitch("disable-http-cache");
  app.commandLine.appendSwitch("disable-background-networking");
  app.commandLine.appendSwitch("disable-gpu");
} catch {}
app.disableHardwareAcceleration();

app.setName("XtremeGuard Parking");
app.setAppUserModelId("XtremeGuardParking");

// -------------------- SINGLE INSTANCE --------------------
const gotLock = app.requestSingleInstanceLock();
let mainWindow = null;
let isQuitting = false;

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

// Track only what we start
const services = {
  // Ports
  phpCgi: {
    key: "phpCgi",
    name: "PHP-CGI",
    port: 9000,
    proc: null,
    startedByUs: false,
  },
  nginx: {
    key: "nginx",
    name: "Nginx",
    port: 3000,
    proc: null,
    startedByUs: false,
  },
  mosquitto: {
    key: "mosquitto",
    name: "Mosquitto",
    port: 1883,
    proc: null,
    startedByUs: false,
  },
  artisanServe: {
    key: "artisanServe",
    name: "ArtisanServe",
    port: 8000,
    proc: null,
    startedByUs: false,
  },

  // Artisan workers (no port)
  mqttSub: {
    key: "mqttSub",
    name: "MQTT",
    port: null,
    proc: null,
    startedByUs: false,
  },
  mqttQr: {
    key: "mqttQr",
    name: "MQTT-QR",
    port: null,
    proc: null,
    startedByUs: false,
  },
};

let MACHINE_ID = null;

// -------------------- EXTERNAL CONFIG (ProgramData) --------------------
const PRODUCT_NAME = "XtremeGuardParking";
const PROGRAM_DATA = process.env.ProgramData || null;

const BASE_DATA_DIR = PROGRAM_DATA
  ? path.join(PROGRAM_DATA, PRODUCT_NAME)
  : path.join(app.getPath("userData"), PRODUCT_NAME);

const LOG_BASE = path.join(BASE_DATA_DIR, "logs");
const CONFIG_PATH = path.join(BASE_DATA_DIR, "config.json");

const DEFAULT_CONFIG = {
  debugLogs: false,
  cameraMuteConsole: true,
  alwaysLogCritical: true,
  watchdogEnabled: true,
  watchdogIntervalMs: 8000,
};

let RUNTIME_CONFIG = { ...DEFAULT_CONFIG };

function ensureBaseDataDir() {
  try {
    fs.mkdirSync(BASE_DATA_DIR, { recursive: true });
  } catch {}
}

function safeParseJson(raw) {
  try {
    return JSON.parse(raw);
  } catch {
    return null;
  }
}

function readRuntimeConfig() {
  ensureBaseDataDir();

  const argv = process.argv || [];
  const cliDebug = argv.includes("--debug-logs")
    ? true
    : argv.includes("--no-debug-logs")
      ? false
      : undefined;

  let fileCfg = null;
  try {
    if (fs.existsSync(CONFIG_PATH)) {
      const raw = fs.readFileSync(CONFIG_PATH, "utf8");
      fileCfg = safeParseJson(raw);
    }
  } catch {}

  let merged = { ...DEFAULT_CONFIG };
  if (fileCfg && typeof fileCfg === "object")
    merged = { ...merged, ...fileCfg };
  if (cliDebug !== undefined) merged.debugLogs = cliDebug;

  RUNTIME_CONFIG = merged;
  return RUNTIME_CONFIG;
}

function startConfigWatcher() {
  readRuntimeConfig();
  setInterval(() => {
    readRuntimeConfig();
  }, 3000);
}

// -------------------- LOGGING --------------------
function getTodayFolderName() {
  const d = new Date();
  const yyyy = d.getFullYear();
  const mm = String(d.getMonth() + 1).padStart(2, "0");
  const dd = String(d.getDate()).padStart(2, "0");
  return `${yyyy}-${mm}-${dd}`;
}

function ensureLogsDir() {
  try {
    ensureBaseDataDir();
    const dayFolder = path.join(LOG_BASE, getTodayFolderName());
    fs.mkdirSync(dayFolder, { recursive: true });
    return dayFolder;
  } catch {
    try {
      fs.mkdirSync(LOG_BASE, { recursive: true });
      return LOG_BASE;
    } catch {
      return app.getPath("userData");
    }
  }
}

function safeName(s) {
  return String(s || "log").replace(/[^a-z0-9_\-]/gi, "_");
}

function writeLog(fileBaseName, message) {
  try {
    const dayFolder = ensureLogsDir();
    const line = `[${new Date().toISOString()}] ${message}\n`;
    fs.appendFileSync(
      path.join(dayFolder, `${safeName(fileBaseName)}.log`),
      line,
      "utf8",
    );
  } catch {}
}

function log(serviceName, message) {
  const cfg = RUNTIME_CONFIG || DEFAULT_CONFIG;

  const isCritical =
    serviceName === "FATAL" ||
    serviceName === "STOP" ||
    serviceName === "Application" ||
    serviceName === "Installer" ||
    serviceName === "WATCHDOG" ||
    serviceName === "SERVICE_CTRL";

  if (!cfg.debugLogs) {
    if (!(cfg.alwaysLogCritical && isCritical)) return;
  }

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
    return parseInt(out || "0", 10) > 0;
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

// -------------------- STATUS UI IPC --------------------
function svcMeta(meta) {
  if (mainWindow && !mainWindow.isDestroyed())
    mainWindow.webContents.send("svc:init", { meta });
}
function svcUpdate(payload) {
  if (mainWindow && !mainWindow.isDestroyed())
    mainWindow.webContents.send("svc:update", payload);
}
function svcReady(meta) {
  if (mainWindow && !mainWindow.isDestroyed())
    mainWindow.webContents.send("svc:ready", { meta });
}
function svcError(meta) {
  if (mainWindow && !mainWindow.isDestroyed())
    mainWindow.webContents.send("svc:error", { meta });
}

// -------------------- GLOBAL ERROR LOGGING --------------------
process.on("uncaughtException", (err) =>
  writeLog("FATAL", err?.stack || String(err)),
);
process.on("unhandledRejection", (reason) =>
  writeLog("FATAL", reason?.stack || String(reason)),
);

app.on("render-process-gone", (_event, _wc, details) => {
  writeLog(
    "ELECTRON",
    `render-process-gone: reason=${details.reason} exitCode=${details.exitCode}`,
  );
});
app.on("child-process-gone", (_event, details) => {
  writeLog(
    "ELECTRON",
    `child-process-gone: type=${details.type} reason=${details.reason} exitCode=${details.exitCode}`,
  );
});

// -------------------- SOCKET INIT --------------------
function initSocketNonInvasive() {
  try {
    require("./socket");
    log("SOCKET", "Socket init requested (./socket).");
  } catch (e) {
    writeLog(
      "SOCKET",
      `Socket init failed: ${e?.stack || e?.message || String(e)}`,
    );
  }
}

// -------------------- DEFERRED ARTISAN STARTER --------------------
async function startDeferredArtisanCommand({
  uiName,
  logName,
  args,
  waitPorts = [],
  timeoutMs = 60000,
}) {
  try {
    svcUpdate({
      name: uiName,
      status: "starting",
      message: waitPorts.length
        ? `Waiting ports: ${waitPorts.join(", ")}…`
        : "Starting…",
    });
    writeLog(
      logName,
      `Deferred start requested. Waiting ports: ${waitPorts.join(", ") || "none"}…`,
    );

    for (const p of waitPorts) {
      svcUpdate({
        name: uiName,
        status: "starting",
        message: `Waiting for port ${p}…`,
      });
      const ok = await waitForPort(p, timeoutMs);
      if (!ok) {
        svcUpdate({
          name: uiName,
          status: "failed",
          message: `Timeout waiting for port ${p}. Not started.`,
        });
        writeLog(
          logName,
          `FAILED: Timeout waiting for port ${p}. Not started.`,
        );
        return null;
      }
    }

    const proc = spawnWrapper(`[${uiName}]`, phpPathCli, ["artisan", ...args], {
      cwd: srcDirectory,
      baseDir: appDir,
    });

    writeLog(
      logName,
      `RUNNING pid=${getPid(proc)} args=artisan ${args.join(" ")}`,
    );
    svcUpdate({
      name: uiName,
      status: "running",
      pid: getPid(proc),
      message: "Started.",
    });
    return proc;
  } catch (e) {
    svcUpdate({
      name: uiName,
      status: "failed",
      message: "Start exception. Check logs.",
    });
    writeLog(logName, `FAILED: ${e?.stack || e?.message || String(e)}`);
    return null;
  }
}

// -------------------- CAMERA SERVICES (ORDER: CAM2, CAM3, CAM1) --------------------
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
      logFile: CAMERA_LOG_FILES.cam2,
      file: "./camera_2_start_camera_live_stream",
    },
    { logFile: CAMERA_LOG_FILES.cam3, file: "./camera_3_watchCameraImages" },
    {
      logFile: CAMERA_LOG_FILES.cam1,
      file: "./camera_1_organize_files_by_date",
    },
  ];

  for (const s of list) {
    const fileOnlyLogger = (msg) => writeLog(s.logFile, msg);
    const fileOnlyBeat = (msg) => writeLog(s.logFile, `HEARTBEAT: ${msg}`);

    const cfg = RUNTIME_CONFIG || DEFAULT_CONFIG;
    const shouldMuteConsole = cfg.cameraMuteConsole && !cfg.debugLogs;

    const originalConsole = {
      log: console.log,
      error: console.error,
      warn: console.warn,
      info: console.info,
      debug: console.debug,
    };

    try {
      if (shouldMuteConsole) {
        console.log = () => {};
        console.error = () => {};
        console.warn = () => {};
        console.info = () => {};
        console.debug = () => {};
      }

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
      console.log = originalConsole.log;
      console.error = originalConsole.error;
      console.warn = originalConsole.warn;
      console.info = originalConsole.info;
      console.debug = originalConsole.debug;
    }
  }
}

async function startCameraAfterLaravelReady() {
  try {
    writeLog(
      "camera_boot",
      "Waiting for Laravel port 8000 before starting camera modules...",
    );

    if (isPortListening(8000)) {
      writeLog(
        "camera_boot",
        "Laravel already listening on 8000. Starting camera modules...",
      );
      initCameraServicesNonInvasive();
      return;
    }

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
    webPreferences: { nodeIntegration: true, contextIsolation: false },
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

  mainWindow.on("closed", () => (mainWindow = null));
  return mainWindow;
}

// -------------------- IPC HELPERS --------------------
ipcMain.handle("open-login", async () => {
  if (mainWindow && !mainWindow.isDestroyed()) {
    await mainWindow.loadURL("http://127.0.0.1:3000/login");
  }
});

ipcMain.handle("open-logs-folder", async () => {
  const folder = ensureLogsDir();
  try {
    await shell.openPath(folder);
  } catch {}
  return folder;
});

ipcMain.handle("get-runtime-config", async () => {
  return {
    configPath: CONFIG_PATH,
    logBase: LOG_BASE,
    todayLogFolder: ensureLogsDir(),
    config: RUNTIME_CONFIG,
  };
});

// -------------------- WATCHDOG + MANUAL START/RESTART --------------------
const restartState = { lastRestartAt: {}, restartCount: {}, restarting: {} };

// Backoff: 2s, 5s, 10s, 20s, 30s
function getBackoffMs(key) {
  const n = restartState.restartCount[key] || 0;
  const seq = [2000, 5000, 10000, 20000, 30000];
  return seq[Math.min(n, seq.length - 1)];
}
function canRestartNow(key) {
  const now = Date.now();
  const last = restartState.lastRestartAt[key] || 0;
  return now - last >= getBackoffMs(key);
}
function markRestart(key) {
  restartState.lastRestartAt[key] = Date.now();
  restartState.restartCount[key] = (restartState.restartCount[key] || 0) + 1;
}
function markHealthy(key) {
  restartState.restartCount[key] = 0;
  restartState.lastRestartAt[key] = 0;
}

// -------------------- MQTT STORM FIX (single-flight + grace) --------------------
let mqttStartInProgress = false;
let mqttQrStartInProgress = false;

let mqttLastStartAt = 0;
let mqttQrLastStartAt = 0;

const MQTT_GRACE_MS = 30000;

// -------------------- STARTERS --------------------
async function startMosquitto() {
  if (isPortListening(1883)) {
    svcUpdate({
      name: services.mosquitto.name,
      status: "running",
      port: 1883,
      message: "Already running.",
    });
    markHealthy("mosquitto");
    return "already-running";
  }

  if (!mosquittoPath || !mosquittoConf) {
    svcUpdate({
      name: services.mosquitto.name,
      status: "failed",
      port: 1883,
      message: "Mosquitto exe/conf not found.",
    });
    writeLog("Mosquitto", "FAILED: Mosquitto exe/conf not found.");
    return "failed";
  }

  svcUpdate({
    name: services.mosquitto.name,
    status: "starting",
    port: 1883,
    message: "Starting broker…",
  });
  writeLog("Mosquitto", "Starting broker…");

  services.mosquitto.proc = spawnWrapper(
    "[Mosquitto]",
    mosquittoPath,
    ["-c", mosquittoConf, "-v"],
    {
      cwd: path.dirname(mosquittoPath),
      baseDir: appDir,
    },
  );
  services.mosquitto.startedByUs = true;

  const ok = await waitForPort(1883, 25000);
  const pid = getPid(services.mosquitto.proc);

  svcUpdate({
    name: services.mosquitto.name,
    status: ok ? "running" : "failed",
    port: 1883,
    pid,
    message: ok
      ? "Listening on 1883."
      : "Not listening. Check logs/conf/firewall.",
  });
  writeLog(
    "Mosquitto",
    ok ? `RUNNING pid=${pid} port=1883` : `FAILED pid=${pid} port=1883`,
  );

  if (ok) markHealthy("mosquitto");
  return ok ? "started" : "failed";
}

async function startArtisanServe() {
  if (isPortListening(8000)) {
    svcUpdate({
      name: services.artisanServe.name,
      status: "running",
      port: 8000,
      message: "Already running.",
    });
    markHealthy("artisanServe");
    return "already-running";
  }

  svcUpdate({
    name: services.artisanServe.name,
    status: "starting",
    port: 8000,
    message: "Starting artisan serve…",
  });
  writeLog("ArtisanServe", "Starting artisan serve…");

  services.artisanServe.proc = spawnWrapper(
    "[ArtisanServe]",
    phpPathCli,
    ["artisan", "serve", "--host=0.0.0.0", "--port=8000"],
    { cwd: srcDirectory, baseDir: appDir },
  );
  services.artisanServe.startedByUs = true;

  const ok = await waitForPort(8000, 25000);
  const pid = getPid(services.artisanServe.proc);

  svcUpdate({
    name: services.artisanServe.name,
    status: ok ? "running" : "failed",
    port: 8000,
    pid,
    message: ok
      ? "Listening on 8000."
      : "Not listening. Check Laravel boot logs.",
  });
  writeLog(
    "ArtisanServe",
    ok ? `RUNNING pid=${pid} port=8000` : `FAILED pid=${pid} port=8000`,
  );

  if (ok) markHealthy("artisanServe");
  return ok ? "started" : "failed";
}

async function startPhpCgi() {
  if (isPortListening(9000)) {
    svcUpdate({
      name: services.phpCgi.name,
      status: "running",
      port: 9000,
      message: "Already running.",
    });
    markHealthy("phpCgi");
    return "already-running";
  }

  svcUpdate({
    name: services.phpCgi.name,
    status: "starting",
    port: 9000,
    message: "Starting php-cgi worker…",
  });
  writeLog("PHP-CGI", "Starting php-cgi worker…");

  services.phpCgi.proc = spawnPhpCgiWorker(phpCGi, 9000, {
    cwd: srcDirectory,
    baseDir: appDir,
  });
  services.phpCgi.startedByUs = true;

  const ok = await waitForPort(9000, 25000);
  const pid = getPid(services.phpCgi.proc);

  svcUpdate({
    name: services.phpCgi.name,
    status: ok ? "running" : "failed",
    port: 9000,
    pid,
    message: ok ? "Listening on 9000." : "Not listening. Check PHP-CGI logs.",
  });
  writeLog(
    "PHP-CGI",
    ok ? `RUNNING pid=${pid} port=9000` : `FAILED pid=${pid} port=9000`,
  );

  if (ok) markHealthy("phpCgi");
  return ok ? "started" : "failed";
}

async function startNginx() {
  if (isPortListening(3000)) {
    svcUpdate({
      name: services.nginx.name,
      status: "running",
      port: 3000,
      message: "Already running.",
    });
    markHealthy("nginx");
    return "already-running";
  }

  svcUpdate({
    name: services.nginx.name,
    status: "starting",
    port: 3000,
    message: "Starting nginx…",
  });
  writeLog("Nginx", "Starting nginx…");

  services.nginx.proc = spawnWrapper(
    "[Nginx]",
    nginxPath,
    ["-p", appDir, "-c", "conf/nginx.conf"],
    {
      cwd: appDir,
      baseDir: appDir,
    },
  );
  services.nginx.startedByUs = true;

  const ok = await waitForPort(3000, 30000);
  const pid = getPid(services.nginx.proc);

  svcUpdate({
    name: services.nginx.name,
    status: ok ? "running" : "failed",
    port: 3000,
    pid,
    message: ok
      ? "Listening on 3000."
      : "Not listening. Check nginx logs/conf.",
  });
  writeLog(
    "Nginx",
    ok ? `RUNNING pid=${pid} port=3000` : `FAILED pid=${pid} port=3000`,
  );

  if (ok) markHealthy("nginx");
  return ok ? "started" : "failed";
}

function startMqttDeferredNonBlocking() {
  if (mqttStartInProgress) {
    writeLog(
      "MQTT",
      "Start requested but already in progress. Ignoring duplicate.",
    );
    return "ignored";
  }
  mqttStartInProgress = true;

  startDeferredArtisanCommand({
    uiName: "MQTT",
    logName: "MQTT",
    args: ["mqtt:subscribe"],
    waitPorts: [1883, 8000],
  })
    .then((proc) => {
      services.mqttSub.proc = proc;
      services.mqttSub.startedByUs = !!proc;
      mqttLastStartAt = Date.now();
      if (proc) markHealthy("mqttSub");
    })
    .finally(() => {
      mqttStartInProgress = false;
    });

  return "queued";
}

function startMqttQrDeferredNonBlocking() {
  if (mqttQrStartInProgress) {
    writeLog(
      "MQTT-QR",
      "Start requested but already in progress. Ignoring duplicate.",
    );
    return "ignored";
  }
  mqttQrStartInProgress = true;

  startDeferredArtisanCommand({
    uiName: "MQTT-QR",
    logName: "MQTT-QR",
    args: ["mqtt:qrbackgroundlistener"],
    waitPorts: [1883, 8000],
  })
    .then((proc) => {
      services.mqttQr.proc = proc;
      services.mqttQr.startedByUs = !!proc;
      mqttQrLastStartAt = Date.now();
      if (proc) markHealthy("mqttQr");
    })
    .finally(() => {
      mqttQrStartInProgress = false;
    });

  return "queued";
}

async function startServiceByKey(key, reason = "manual") {
  if (!key) return { ok: false, message: "Missing service key" };
  if (restartState.restarting[key])
    return { ok: false, message: "Already starting/restarting" };

  restartState.restarting[key] = true;
  try {
    writeLog("SERVICE_CTRL", `startServiceByKey(${key}) reason=${reason}`);

    switch (key) {
      case "mqttSub":
        svcUpdate({
          name: services.mqttSub.name,
          status: "starting",
          message: "Starting (deferred)…",
        });
        startMqttDeferredNonBlocking();
        return { ok: true, message: "MQTT queued (deferred)" };

      case "mqttQr":
        svcUpdate({
          name: services.mqttQr.name,
          status: "starting",
          message: "Starting (deferred)…",
        });
        startMqttQrDeferredNonBlocking();
        return { ok: true, message: "MQTT-QR queued (deferred)" };

      case "phpCgi":
        return {
          ok: (await startPhpCgi()) !== "failed",
          message: "PHP-CGI start attempted",
        };

      case "nginx":
        return {
          ok: (await startNginx()) !== "failed",
          message: "Nginx start attempted",
        };

      case "mosquitto":
        return {
          ok: (await startMosquitto()) !== "failed",
          message: "Mosquitto start attempted",
        };

      case "artisanServe":
        return {
          ok: (await startArtisanServe()) !== "failed",
          message: "ArtisanServe start attempted",
        };

      default:
        return { ok: false, message: `Unknown service key: ${key}` };
    }
  } finally {
    restartState.restarting[key] = false;
  }
}

async function restartServiceByKey(key, reason = "manual") {
  writeLog("SERVICE_CTRL", `restartServiceByKey(${key}) reason=${reason}`);

  const svc = services[key];
  if (svc && svc.startedByUs && svc.proc) {
    try {
      await stopServices(svc.proc);
    } catch {}
    svc.proc = null;
    svc.startedByUs = false;
  }

  // manual restart should not be blocked by backoff
  restartState.lastRestartAt[key] = 0;
  restartState.restartCount[key] = 0;

  return await startServiceByKey(key, reason);
}

// UI buttons -> start/restart
ipcMain.handle("service:start", async (_e, key) =>
  startServiceByKey(key, "ui-start"),
);
ipcMain.handle("service:restart", async (_e, key) =>
  restartServiceByKey(key, "ui-restart"),
);

// Auto-restart watchdog
function startServiceWatchdog() {
  const cfg = RUNTIME_CONFIG || DEFAULT_CONFIG;
  if (!cfg.watchdogEnabled) {
    writeLog("WATCHDOG", "Watchdog disabled by config.");
    return;
  }

  const intervalMs = Number(cfg.watchdogIntervalMs || 8000);
  writeLog("WATCHDOG", `Watchdog started. intervalMs=${intervalMs}`);

  setInterval(async () => {
    const localCfg = RUNTIME_CONFIG || DEFAULT_CONFIG;
    if (!localCfg.watchdogEnabled) return;

    const checks = [
      { key: "phpCgi", type: "port", port: 9000, name: "PHP-CGI" },
      { key: "nginx", type: "port", port: 3000, name: "Nginx" },
      { key: "mosquitto", type: "port", port: 1883, name: "Mosquitto" },
      { key: "artisanServe", type: "port", port: 8000, name: "ArtisanServe" },
      { key: "mqttSub", type: "pid", name: "MQTT" },
      { key: "mqttQr", type: "pid", name: "MQTT-QR" },
    ];

    for (const c of checks) {
      const svc = services[c.key];
      if (!svc || !svc.startedByUs) continue;

      let healthy = true;
      if (c.type === "port") healthy = isPortListening(c.port);
      if (c.type === "pid") healthy = isPidAlive(getPid(svc.proc));

      if (healthy) {
        markHealthy(c.key);
        continue;
      }

      // MQTT storm protection: if just started, do NOT restart yet
      if (c.key === "mqttSub") {
        if (mqttStartInProgress) continue;
        if (mqttLastStartAt && Date.now() - mqttLastStartAt < MQTT_GRACE_MS)
          continue;
      }
      if (c.key === "mqttQr") {
        if (mqttQrStartInProgress) continue;
        if (mqttQrLastStartAt && Date.now() - mqttQrLastStartAt < MQTT_GRACE_MS)
          continue;
      }

      if (!canRestartNow(c.key)) continue;
      if (restartState.restarting[c.key]) continue;

      svcUpdate({
        name: svc.name,
        status: "failed",
        port: c.port || null,
        message: "Detected stopped. Watchdog restarting…",
      });
      writeLog("WATCHDOG", `${c.name} unhealthy. key=${c.key}. Restarting.`);

      markRestart(c.key);
      await restartServiceByKey(c.key, "watchdog");
    }
  }, intervalMs);
}

// -------------------- START SERVICES (ORDER AS REQUESTED) --------------------
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

  svcMeta("Starting services in requested order…");

  // 1) MQTT
  svcUpdate({
    name: services.mqttSub.name,
    status: "starting",
    message: "Waiting ports: 1883, 8000…",
  });
  startMqttDeferredNonBlocking();
  await sleep(1000);

  // 2) MQTT-QR
  svcUpdate({
    name: services.mqttQr.name,
    status: "starting",
    message: "Waiting ports: 1883, 8000…",
  });
  startMqttQrDeferredNonBlocking();
  await sleep(1000);

  // 3) PHP-CGI
  await startPhpCgi();
  await sleep(1200);

  // 4) Nginx
  await startNginx();
  await sleep(1200);

  // 5) Mosquitto
  await startMosquitto();
  await sleep(1200);

  // 6) ArtisanServe
  await startArtisanServe();
  await sleep(1200);

  if (isPortListening(3000)) {
    svcReady("UI is ready on port 3000. Click Open Login (3000).");
  } else {
    svcError("UI is not ready (port 3000 not listening). Check nginx logs.");
  }

  writeLog(
    "Application",
    `Local UI: http://127.0.0.1:3000/login | LAN UI: http://${ipv4Address}:3000/login`,
  );
}

// -------------------- APP READY --------------------
app.whenReady().then(async () => {
  ensureBaseDataDir();
  ensureLogsDir();
  startConfigWatcher();

  try {
    cleanupOldLogs(2, LOG_BASE);
  } catch {
    try {
      cleanupOldLogs(2);
    } catch {}
  }

  writeLog("Application", "App starting...");
  writeLog("Application", `Config: ${CONFIG_PATH}`);
  writeLog("Application", `Logs: ${LOG_BASE}`);

  if (isClockTampered()) {
    writeLog("Application", "System time tamper detected. Exiting.");
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
        // 1) FIRST TIME: start everything once (no watchdog yet)
        await startMissingServicesNonInvasive();

        // 2) AFTER initial startup is done: start watchdog every 5 minutes
        startServiceWatchdog();

        // 3) camera after laravel ready
        await startCameraAfterLaravelReady();
      })
      .catch(async (err) => {
        writeLog("Installer", err?.stack || err?.message || String(err));

        // same flow even if installer fails
        await startMissingServicesNonInvasive();
        startServiceWatchdog();
        await startCameraAfterLaravelReady();
      });
  });
});

// -------------------- CLEAN QUIT --------------------
async function stopOnlyWhatWeStarted() {
  for (const key of Object.keys(services)) {
    const svc = services[key];
    if (svc.startedByUs && svc.proc) {
      try {
        await stopServices(svc.proc);
        writeLog(svc.name, "Stopped (requested).");
      } catch (e) {
        writeLog(
          "STOP",
          `${svc.name} stop error: ${e?.stack || e?.message || String(e)}`,
        );
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

  writeLog("Application", "before-quit fired. Stopping started services...");
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
  writeLog("Application", "will-quit fired.");
  try {
    fs.appendFileSync(
      path.join(ensureLogsDir(), "XtremeGuardParking_SHUTDOWN.txt"),
      `will-quit fired ${new Date().toISOString()}\n`,
    );
  } catch {}
});
