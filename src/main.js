/**
 * main.js — FULL CODE (STRICT one-by-one + artisan ALWAYS from /www via native spawn)
 *
 * Fixes included:
 * 1) ALL php artisan commands are started using Node native spawn() with cwd=www (srcDirectory)
 *    - This avoids spawnWrapper returning pid=null and hides errors
 *    - Captures STDOUT/STDERR into ProgramData logs so you can see real errors
 * 2) Services start ONE BY ONE (blocking) — no overlaps
 * 3) Watchdog runs every 5 minutes (low CPU). Starts AFTER initial startup.
 * 4) If Laravel MQTT / Laravel MQTT-QR fails => restart Mosquitto then restart the worker
 * 5) UI buttons: Start / Restart services via ipc "service:start" & "service:restart"
 * 6) Logs stored in ProgramData\XtremeGuardParking\logs\YYYY-MM-DD\
 * 7) Cameras start only after Laravel 8000 is listening (order: cam2, cam3, cam1)
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
const { execSync, spawn } = require("child_process");

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

// Track only what we start (so we stop only these)
const services = {
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
  mqttSub: {
    key: "mqttSub",
    name: "Laravel MQTT",
    port: null,
    proc: null,
    startedByUs: false,
  },
  mqttQr: {
    key: "mqttQr",
    name: "Laravel MQTT-QR",
    port: null,
    proc: null,
    startedByUs: false,
  },
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
};

let MACHINE_ID = null;

// -------------------- EXTERNAL DATA DIR (ProgramData) --------------------
const PRODUCT_NAME = "XtremeGuardParking";
const PROGRAM_DATA = process.env.ProgramData || null;

const BASE_DATA_DIR = PROGRAM_DATA
  ? path.join(PROGRAM_DATA, PRODUCT_NAME)
  : path.join(app.getPath("userData"), PRODUCT_NAME);

const LOG_BASE = path.join(BASE_DATA_DIR, "logs");
const CONFIG_PATH = path.join(BASE_DATA_DIR, "config.json");

const DEFAULT_CONFIG = {
  debugLogs: false,
  alwaysLogCritical: true,
  cameraMuteConsole: true,
  watchdogEnabled: true,
  watchdogIntervalMs: 300000, // ✅ 5 minutes
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
  setInterval(() => readRuntimeConfig(), 3000);
}

// -------------------- LOGGING (ProgramData\logs\YYYY-MM-DD) --------------------
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

  if (!cfg.debugLogs && !(cfg.alwaysLogCritical && isCritical)) return;

  try {
    logger(serviceName, message);
  } catch {}
  writeLog(serviceName, message);
}

function sleep(ms) {
  return new Promise((resolve) => setTimeout(resolve, ms));
}

// -------------------- PORT / PID --------------------
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

async function confirmPidStable(proc, stableMs = 3000) {
  const pid = getPid(proc);
  if (!pid) return false;
  if (!isPidAlive(pid)) return false;
  await sleep(stableMs);
  return isPidAlive(pid);
}

// Detect existing artisan worker started outside Electron (avoid duplicates)
function findRunningArtisanProcessPid(artisanSubCommand) {
  try {
    const ps = `
      $cmd = "${artisanSubCommand}".ToLower();
      $p = Get-CimInstance Win32_Process |
        Where-Object { $_.CommandLine -and $_.CommandLine.ToLower().Contains("artisan") -and $_.CommandLine.ToLower().Contains($cmd) } |
        Select-Object -First 1 ProcessId;
      if ($p) { $p.ProcessId } else { "" }
    `;
    const out = execSync(
      `powershell -NoProfile -Command "${ps.replace(/\r?\n/g, " ")}"`,
      {
        stdio: ["ignore", "pipe", "ignore"],
      },
    )
      .toString()
      .trim();

    const pid = parseInt(out || "0", 10);
    return pid > 0 ? pid : null;
  } catch {
    return null;
  }
}

function attachExitLogging(serviceLogName, child) {
  if (!child || typeof child.on !== "function") return;
  child.on("exit", (code, signal) => {
    writeLog(
      serviceLogName,
      `PROCESS EXITED code=${code} signal=${signal || ""}`,
    );
  });
  child.on("error", (err) => {
    writeLog(
      serviceLogName,
      `PROCESS ERROR: ${err?.stack || err?.message || String(err)}`,
    );
  });
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

// -------------------- ARTISAN (NATIVE SPAWN) ALWAYS /www --------------------
function assertFileExists(label, filePath) {
  const ok = !!filePath && fs.existsSync(filePath);
  if (!ok) writeLog("Application", `MISSING: ${label} => ${filePath}`);
  return ok;
}

/**
 * spawnArtisanNative
 * - uses Node spawn() directly (NOT spawnWrapper)
 * - cwd is always srcDirectory (www)
 * - captures stdout/stderr to log
 * - returns ChildProcess or null
 */
function spawnArtisanNative(serviceLabel, artisanArgs, logFileBase) {
  const okPhp = assertFileExists("php.exe", phpPathCli);
  const okArtisan = assertFileExists(
    "www/artisan",
    path.join(srcDirectory, "artisan"),
  );
  if (!okPhp || !okArtisan) return null;

  writeLog(logFileBase || serviceLabel, `cwd=${srcDirectory}`);
  writeLog(logFileBase || serviceLabel, `php=${phpPathCli}`);
  writeLog(
    logFileBase || serviceLabel,
    `cmd=php artisan ${artisanArgs.join(" ")}`,
  );

  const child = spawn(phpPathCli, ["artisan", ...artisanArgs], {
    cwd: srcDirectory, // ✅ always www
    env: { ...process.env },
    windowsHide: true,
  });

  if (child.stdout) {
    child.stdout.on("data", (d) =>
      writeLog(logFileBase || serviceLabel, `STDOUT: ${String(d).trimEnd()}`),
    );
  }
  if (child.stderr) {
    child.stderr.on("data", (d) =>
      writeLog(logFileBase || serviceLabel, `STDERR: ${String(d).trimEnd()}`),
    );
  }

  child.on("error", (err) => {
    writeLog(
      logFileBase || serviceLabel,
      `SPAWN ERROR: ${err?.stack || err?.message || String(err)}`,
    );
  });

  child.on("exit", (code, signal) => {
    writeLog(
      logFileBase || serviceLabel,
      `PROCESS EXITED code=${code} signal=${signal || ""}`,
    );
  });

  writeLog(logFileBase || serviceLabel, `SPAWNED pid=${child.pid}`);
  return child;
}

// -------------------- STARTERS (STRICT one-by-one) --------------------
async function startMosquittoStrict() {
  const svc = services.mosquitto;
  if (isPortListening(1883)) {
    svcUpdate({
      name: svc.name,
      status: "running",
      port: 1883,
      message: "Already running.",
    });
    return true;
  }

  if (!mosquittoPath || !mosquittoConf) {
    svcUpdate({
      name: svc.name,
      status: "failed",
      port: 1883,
      message: "Mosquitto exe/conf not found.",
    });
    writeLog("Mosquitto", "FAILED: exe/conf not found.");
    return false;
  }

  svcUpdate({
    name: svc.name,
    status: "starting",
    port: 1883,
    message: "Starting broker…",
  });
  writeLog("Mosquitto", "Starting broker…");

  svc.proc = spawnWrapper(
    "[Mosquitto]",
    mosquittoPath,
    ["-c", mosquittoConf, "-v"],
    {
      cwd: path.dirname(mosquittoPath),
      baseDir: appDir,
    },
  );
  svc.startedByUs = true;
  attachExitLogging("Mosquitto", svc.proc);

  const ok = await waitForPort(1883, 30000);
  const pid = getPid(svc.proc);

  svcUpdate({
    name: svc.name,
    status: ok ? "running" : "failed",
    port: 1883,
    pid,
    message: ok
      ? "Listening on 1883."
      : "Not listening. Check mosquitto conf/logs.",
  });
  writeLog("Mosquitto", ok ? `RUNNING pid=${pid}` : `FAILED pid=${pid}`);

  return ok;
}

async function startArtisanServeStrict() {
  const svc = services.artisanServe;
  if (isPortListening(8000)) {
    svcUpdate({
      name: svc.name,
      status: "running",
      port: 8000,
      message: "Already running.",
    });
    return true;
  }

  svcUpdate({
    name: svc.name,
    status: "starting",
    port: 8000,
    message: "Starting artisan serve…",
  });
  writeLog("ArtisanServe", "Starting artisan serve… (cwd=www)");

  svc.proc = spawnArtisanNative(
    "ArtisanServe",
    ["serve", "--host=0.0.0.0", "--port=8000"],
    "ArtisanServe",
  );
  svc.startedByUs = true;

  if (!svc.proc || !getPid(svc.proc)) {
    svcUpdate({
      name: svc.name,
      status: "failed",
      port: 8000,
      message:
        "Spawn failed (no PID). Check ArtisanServe log (STDERR/SPAWN ERROR).",
    });
    writeLog("ArtisanServe", "FAILED: spawn returned null or pid missing.");
    return false;
  }

  const ok = await waitForPort(8000, 30000);
  const pid = getPid(svc.proc);

  svcUpdate({
    name: svc.name,
    status: ok ? "running" : "failed",
    port: 8000,
    pid,
    message: ok ? "Listening on 8000." : "Not listening. Check Laravel logs.",
  });
  writeLog("ArtisanServe", ok ? `RUNNING pid=${pid}` : `FAILED pid=${pid}`);

  return ok;
}

async function startLaravelMqttStrict() {
  const svc = services.mqttSub;

  const existingPid = findRunningArtisanProcessPid("mqtt:subscribe");
  if (existingPid) {
    svcUpdate({
      name: svc.name,
      status: "running",
      pid: existingPid,
      message: "Already running (existing process). Not starting another.",
    });
    writeLog(
      "Laravel_MQTT",
      `Detected existing mqtt:subscribe pid=${existingPid}. Skipping spawn.`,
    );
    svc.proc = null;
    svc.startedByUs = false;
    return true;
  }

  svcUpdate({
    name: svc.name,
    status: "starting",
    message: "Waiting Mosquitto(1883) + Laravel(8000)...",
  });
  writeLog("Laravel_MQTT", "Waiting for ports 1883 and 8000...");

  const ok1883 = await waitForPort(1883, 60000);
  const ok8000 = await waitForPort(8000, 60000);
  if (!ok1883 || !ok8000) {
    svcUpdate({
      name: svc.name,
      status: "failed",
      message: "Dependencies not ready (1883/8000).",
    });
    writeLog("Laravel_MQTT", "FAILED: deps not ready.");
    return false;
  }

  svcUpdate({
    name: svc.name,
    status: "starting",
    message: "Starting artisan mqtt:subscribe…",
  });
  writeLog("Laravel_MQTT", "Starting mqtt:subscribe (cwd=www)");

  svc.proc = spawnArtisanNative(
    "Laravel MQTT",
    ["mqtt:subscribe"],
    "Laravel_MQTT",
  );
  svc.startedByUs = true;

  if (!svc.proc || !getPid(svc.proc)) {
    svcUpdate({
      name: svc.name,
      status: "failed",
      message:
        "Spawn failed (no PID). Check Laravel_MQTT log (STDERR/SPAWN ERROR).",
    });
    writeLog("Laravel_MQTT", "FAILED: spawn returned null or pid missing.");
    return false;
  }

  const pid = getPid(svc.proc);
  const stable = await confirmPidStable(svc.proc, 3000);

  svcUpdate({
    name: svc.name,
    status: stable ? "running" : "failed",
    pid,
    message: stable ? "Started." : "Exited quickly. Check Laravel_MQTT.log",
  });

  writeLog(
    "Laravel_MQTT",
    stable ? `RUNNING pid=${pid}` : `FAILED (exited) pid=${pid}`,
  );
  return stable;
}

async function startLaravelMqttQrStrict() {
  const svc = services.mqttQr;

  const existingPid = findRunningArtisanProcessPid("mqtt:qrbackgroundlistener");
  if (existingPid) {
    svcUpdate({
      name: svc.name,
      status: "running",
      pid: existingPid,
      message: "Already running (existing process). Not starting another.",
    });
    writeLog(
      "Laravel_MQTT_QR",
      `Detected existing mqtt:qrbackgroundlistener pid=${existingPid}. Skipping spawn.`,
    );
    svc.proc = null;
    svc.startedByUs = false;
    return true;
  }

  svcUpdate({
    name: svc.name,
    status: "starting",
    message: "Waiting Mosquitto(1883) + Laravel(8000)...",
  });
  writeLog("Laravel_MQTT_QR", "Waiting for ports 1883 and 8000...");

  const ok1883 = await waitForPort(1883, 60000);
  const ok8000 = await waitForPort(8000, 60000);
  if (!ok1883 || !ok8000) {
    svcUpdate({
      name: svc.name,
      status: "failed",
      message: "Dependencies not ready (1883/8000).",
    });
    writeLog("Laravel_MQTT_QR", "FAILED: deps not ready.");
    return false;
  }

  svcUpdate({
    name: svc.name,
    status: "starting",
    message: "Starting artisan mqtt:qrbackgroundlistener…",
  });
  writeLog("Laravel_MQTT_QR", "Starting mqtt:qrbackgroundlistener (cwd=www)");

  svc.proc = spawnArtisanNative(
    "Laravel MQTT-QR",
    ["mqtt:qrbackgroundlistener"],
    "Laravel_MQTT_QR",
  );
  svc.startedByUs = true;

  if (!svc.proc || !getPid(svc.proc)) {
    svcUpdate({
      name: svc.name,
      status: "failed",
      message:
        "Spawn failed (no PID). Check Laravel_MQTT_QR log (STDERR/SPAWN ERROR).",
    });
    writeLog("Laravel_MQTT_QR", "FAILED: spawn returned null or pid missing.");
    return false;
  }

  const pid = getPid(svc.proc);
  const stable = await confirmPidStable(svc.proc, 3000);

  svcUpdate({
    name: svc.name,
    status: stable ? "running" : "failed",
    pid,
    message: stable ? "Started." : "Exited quickly. Check Laravel_MQTT_QR.log",
  });

  writeLog(
    "Laravel_MQTT_QR",
    stable ? `RUNNING pid=${pid}` : `FAILED (exited) pid=${pid}`,
  );
  return stable;
}

async function startPhpCgiStrict() {
  const svc = services.phpCgi;
  if (isPortListening(9000)) {
    svcUpdate({
      name: svc.name,
      status: "running",
      port: 9000,
      message: "Already running.",
    });
    return true;
  }

  svcUpdate({
    name: svc.name,
    status: "starting",
    port: 9000,
    message: "Starting php-cgi worker…",
  });
  writeLog("PHP-CGI", "Starting php-cgi worker…");

  svc.proc = spawnPhpCgiWorker(phpCGi, 9000, {
    cwd: srcDirectory,
    baseDir: appDir,
  });
  svc.startedByUs = true;
  attachExitLogging("PHP-CGI", svc.proc);

  const ok = await waitForPort(9000, 30000);
  const pid = getPid(svc.proc);

  svcUpdate({
    name: svc.name,
    status: ok ? "running" : "failed",
    port: 9000,
    pid,
    message: ok ? "Listening on 9000." : "Not listening. Check PHP-CGI logs.",
  });
  writeLog("PHP-CGI", ok ? `RUNNING pid=${pid}` : `FAILED pid=${pid}`);

  return ok;
}

async function startNginxStrict() {
  const svc = services.nginx;
  if (isPortListening(3000)) {
    svcUpdate({
      name: svc.name,
      status: "running",
      port: 3000,
      message: "Already running.",
    });
    return true;
  }

  svcUpdate({
    name: svc.name,
    status: "starting",
    port: 3000,
    message: "Starting nginx…",
  });
  writeLog("Nginx", "Starting nginx…");

  svc.proc = spawnWrapper(
    "[Nginx]",
    nginxPath,
    ["-p", appDir, "-c", "conf/nginx.conf"],
    {
      cwd: appDir,
      baseDir: appDir,
    },
  );
  svc.startedByUs = true;
  attachExitLogging("Nginx", svc.proc);

  const ok = await waitForPort(3000, 30000);
  const pid = getPid(svc.proc);

  svcUpdate({
    name: svc.name,
    status: ok ? "running" : "failed",
    port: 3000,
    pid,
    message: ok
      ? "Listening on 3000."
      : "Not listening. Check nginx logs/conf.",
  });
  writeLog("Nginx", ok ? `RUNNING pid=${pid}` : `FAILED pid=${pid}`);

  return ok;
}

// ✅ Start ALL services one-by-one (dependency-safe order)
async function startAllServicesOneByOne() {
  svcMeta("Starting services one by one…");

  // 1) Mosquitto
  await startMosquittoStrict();
  await sleep(1000);

  // 2) ArtisanServe
  await startArtisanServeStrict();
  await sleep(1000);

  // 3) Laravel MQTT
  await startLaravelMqttStrict();
  await sleep(1000);

  // 4) Laravel MQTT-QR
  await startLaravelMqttQrStrict();
  await sleep(1000);

  // 5) PHP-CGI
  await startPhpCgiStrict();
  await sleep(1000);

  // 6) Nginx
  await startNginxStrict();
  await sleep(1000);

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

// -------------------- MQTT FAILURE RULE: restart Mosquitto then restart MQTT --------------------
async function restartMosquittoAndWait(reason = "mqtt-failure") {
  writeLog("Mosquitto", `Restart requested due to ${reason}`);

  if (services.mosquitto.startedByUs && services.mosquitto.proc) {
    try {
      await stopServices(services.mosquitto.proc);
      writeLog("Mosquitto", "Stopped for restart.");
    } catch (e) {
      writeLog("Mosquitto", `Stop error: ${e?.message || String(e)}`);
    }
    services.mosquitto.proc = null;
    services.mosquitto.startedByUs = false;
  }

  const okStart = await startMosquittoStrict();
  if (!okStart) return false;

  const ok = await waitForPort(1883, 30000);
  writeLog(
    "Mosquitto",
    ok ? "Restarted and listening on 1883." : "Restarted but NOT listening.",
  );
  return ok;
}

// -------------------- WATCHDOG (every 5 minutes only) --------------------
let watchdogStarted = false;

function startServiceWatchdog() {
  if (watchdogStarted) return;
  watchdogStarted = true;

  const cfg = RUNTIME_CONFIG || DEFAULT_CONFIG;
  if (!cfg.watchdogEnabled) {
    writeLog("WATCHDOG", "Watchdog disabled by config.");
    return;
  }

  const intervalMs = Number(cfg.watchdogIntervalMs || 300000);
  writeLog("WATCHDOG", `Watchdog started intervalMs=${intervalMs}`);

  setInterval(async () => {
    const localCfg = RUNTIME_CONFIG || DEFAULT_CONFIG;
    if (!localCfg.watchdogEnabled) return;

    // Only check services that we started (very important)
    const checks = [
      { key: "mosquitto", type: "port", port: 1883 },
      { key: "artisanServe", type: "port", port: 8000 },
      { key: "phpCgi", type: "port", port: 9000 },
      { key: "nginx", type: "port", port: 3000 },
      { key: "mqttSub", type: "pid" },
      { key: "mqttQr", type: "pid" },
    ];

    for (const c of checks) {
      const svc = services[c.key];
      if (!svc || !svc.startedByUs) continue;

      let healthy = true;
      if (c.type === "port") healthy = isPortListening(c.port);
      if (c.type === "pid") healthy = isPidAlive(getPid(svc.proc));

      if (healthy) continue;

      // ✅ If MQTT worker fails => restart mosquitto then restart worker
      if (c.key === "mqttSub" || c.key === "mqttQr") {
        writeLog(
          "WATCHDOG",
          `${svc.name} stopped. Restart Mosquitto then restart worker.`,
        );
        svcUpdate({
          name: svc.name,
          status: "failed",
          message: "Stopped. Restarting Mosquitto then worker…",
        });

        const mosqOk = await restartMosquittoAndWait(
          "laravel-mqtt-worker-exit",
        );
        if (!mosqOk) continue;

        if (c.key === "mqttSub") await startLaravelMqttStrict();
        else await startLaravelMqttQrStrict();

        continue;
      }

      // Normal services: restart by type
      writeLog("WATCHDOG", `${svc.name} unhealthy. Restarting.`);
      svcUpdate({
        name: svc.name,
        status: "failed",
        message: "Detected stopped. Restarting…",
      });

      if (c.key === "mosquitto") await startMosquittoStrict();
      if (c.key === "artisanServe") await startArtisanServeStrict();
      if (c.key === "phpCgi") await startPhpCgiStrict();
      if (c.key === "nginx") await startNginxStrict();
    }
  }, intervalMs);
}

// -------------------- CAMERA SERVICES (order: cam2, cam3, cam1) --------------------
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
    const ok = isPortListening(8000) ? true : await waitForPort(8000, 60000);
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

// -------------------- IPC --------------------
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

// UI Start/Restart buttons (strict)
ipcMain.handle("service:start", async (_e, key) => {
  switch (key) {
    case "mosquitto":
      return { ok: await startMosquittoStrict() };
    case "artisanServe":
      return { ok: await startArtisanServeStrict() };
    case "mqttSub":
      return { ok: await startLaravelMqttStrict() };
    case "mqttQr":
      return { ok: await startLaravelMqttQrStrict() };
    case "phpCgi":
      return { ok: await startPhpCgiStrict() };
    case "nginx":
      return { ok: await startNginxStrict() };
    default:
      return { ok: false, message: "Unknown service key" };
  }
});

ipcMain.handle("service:restart", async (_e, key) => {
  const svc = services[key];
  if (svc && svc.startedByUs && svc.proc) {
    try {
      await stopServices(svc.proc);
    } catch {}
    svc.proc = null;
    svc.startedByUs = false;
  }
  return await ipcMain.invoke("service:start", key);
});

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
  writeLog("Application", `www (cwd for artisan): ${srcDirectory}`);
  writeLog("Application", `phpPathCli: ${phpPathCli}`);

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
        // ✅ Start everything STRICT one-by-one
        await startAllServicesOneByOne();

        // ✅ Watchdog after initial startup (every 5 minutes)
        startServiceWatchdog();

        // ✅ Cameras after Laravel 8000
        await startCameraAfterLaravelReady();
      })
      .catch(async (err) => {
        writeLog("Installer", err?.stack || err?.message || String(err));
        await startAllServicesOneByOne();
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
