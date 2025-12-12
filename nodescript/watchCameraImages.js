#!/usr/bin/env node

// Watch *_BACKGROUND files → parse → POST to API (one by one) → publish MQTT → log daily

const mqtt = require("mqtt");
const chokidar = require("chokidar");
const path = require("path");
const fs = require("fs");
const fsp = require("fs/promises");
const axios = require("axios");
require("dotenv").config(); // fallback ONLY

// ============================================================================
// CONFIG LOADED FROM HTTP API
// ============================================================================
let COMPANY_ID = "";
let WATCH_DIR = "";
let MQTT_SERVER = "";
let API_URL = "";
let API_KEY = "";
let TOPIC_EVENT = "";

// API that returns configuration
const CONFIG_ENDPOINT = "http://127.0.0.1:8000/api/get_mqtt_server";

// ---- Logging ----
const logDir = path.resolve(__dirname, "logs");
if (!fs.existsSync(logDir)) fs.mkdirSync(logDir, { recursive: true });

const logFile = path.join(
  logDir,
  `${new Date().toISOString().slice(0, 10)}.log`
);

function logLine(...args) {
  const timestamp = new Date().toISOString();
  const text = args
    .map((a) => (typeof a === "object" ? JSON.stringify(a) : a))
    .join(" ");
  const msg = `[${timestamp}] ${text}\n`;

  fs.appendFile(
    logFile,
    msg,
    (err) => err && console.error("LOG WRITE ERROR:", err)
  );
  console.log(text);
}

// ============================================================================
// PROCESSED FILE PERSISTENCE
// ============================================================================
let processed = new Set();
let processedStorePath = "";
let saveProcessedTimer = null;

async function loadProcessedFromDisk() {
  if (!processedStorePath) return;

  try {
    const data = await fsp.readFile(processedStorePath, "utf8");
    const arr = JSON.parse(data);
    if (Array.isArray(arr)) {
      arr.forEach((f) => processed.add(f));
    }
    logLine(`📂 Loaded ${processed.size} processed filenames from disk.`);
  } catch (err) {
    if (err.code === "ENOENT") {
      logLine("ℹ️ No existing processed-file store found (first run).");
    } else {
      logLine("⚠️ Failed to load processed-file store:", err.message);
    }
  }
}

function scheduleSaveProcessedToDisk() {
  if (!processedStorePath) return;

  if (saveProcessedTimer) clearTimeout(saveProcessedTimer);

  // Debounce saves to avoid excessive disk writes
  saveProcessedTimer = setTimeout(async () => {
    try {
      const arr = Array.from(processed);
      await fsp.writeFile(processedStorePath, JSON.stringify(arr), "utf8");
      logLine(`💾 Saved processed-file store (${arr.length} files).`);
    } catch (err) {
      logLine("❌ Failed to save processed-file store:", err.message);
    }
  }, 500);
}

function markAsProcessed(name) {
  processed.add(name);
  scheduleSaveProcessedToDisk();
}

// ============================================================================
// LOAD ALL CONFIG FROM API
// ============================================================================
async function loadConfig() {
  try {
    logLine("🔄 Loading ALL environment variables from API:", CONFIG_ENDPOINT);

    const res = await axios.get(CONFIG_ENDPOINT, { timeout: 5000 });
    const cfg = res.data || {};

    // Optional: log config once (sanitized)
    logLine("🌐 Raw config from API (sanitized):", {
      COMPANY_ID: cfg.COMPANY_ID,
      WATCH_DIR: cfg.WATCH_DIR,
      MQTT_SERVER: cfg.MQTT_SERVER || cfg.host,
      API_URL: cfg.API_URL,
    });

    // COMPANY_ID
    COMPANY_ID = (cfg.COMPANY_ID ?? "").toString().trim();

    // WATCH_DIR base from API, then append COMPANY_ID if present
    const apiWatchBase = (cfg.WATCH_DIR ?? "").toString().trim();
    if (apiWatchBase) {
      WATCH_DIR = COMPANY_ID
        ? path.join(apiWatchBase, COMPANY_ID)
        : apiWatchBase;
    } else {
      WATCH_DIR = "";
    }

    // MQTT_SERVER from explicit key, or fallback `host`
    MQTT_SERVER = (cfg.MQTT_SERVER ?? cfg.host ?? "").toString().trim();

    // API_URL + API_KEY
    API_URL = (cfg.API_URL ?? "").toString().trim();
    API_KEY = (cfg.API_KEY ?? "").toString().trim();

    logLine("✅ Config loaded from API");
  } catch (err) {
    logLine("❌ Failed API config load. Using .env fallback.", err.message);

    COMPANY_ID = (process.env.COMPANY_ID ?? "").toString().trim();

    const envWatch = (process.env.WATCH_DIR ?? "./inbox").toString().trim();

    WATCH_DIR = COMPANY_ID ? path.join(envWatch, COMPANY_ID) : envWatch;

    MQTT_SERVER = (process.env.MQTT_SERVER ?? "").toString().trim();
    API_URL = (process.env.API_URL ?? "").toString().trim();
    API_KEY = (process.env.API_KEY ?? "").toString().trim();
  }

  // Final validation
  if (!COMPANY_ID || !WATCH_DIR || !MQTT_SERVER || !API_URL) {
    logLine("❌ Missing required configuration after API + fallback.");
    logLine({ COMPANY_ID, WATCH_DIR, MQTT_SERVER, API_URL });
    process.exit(1);
  }

  TOPIC_EVENT = `xtremeparking/${COMPANY_ID}/cameralogs/new_event`;

  logLine("COMPANY_ID :", COMPANY_ID);
  logLine("WATCH_DIR  :", WATCH_DIR);
  logLine("MQTT_SERVER:", MQTT_SERVER);
  logLine("API_URL    :", API_URL);
  logLine("TOPIC_EVENT:", TOPIC_EVENT);
}

// ============================================================================
// STABILITY CHECK FOR FILES
// ============================================================================
async function waitForStableFile(filePath, tries = 5, delayMs = 400) {
  let prev = -1;
  for (let i = 0; i < tries; i++) {
    try {
      const { size } = await fsp.stat(filePath);
      if (size === prev) return true;
      prev = size;
    } catch (err) {
      logLine(
        "⚠️ stat() failed while waiting for stable file:",
        filePath,
        err.message
      );
      return false;
    }
    await new Promise((r) => setTimeout(r, delayMs));
  }
  return false;
}

// ============================================================================
// POST TO API
// ============================================================================
const apiClient = axios.create({
  timeout: 30000,
});

async function postWithRetry(payload, tries = 1) {
  let lastErr;
  for (let i = 1; i <= tries; i++) {
    try {
      return await apiClient.post(API_URL, payload, {
        headers: API_KEY ? { Authorization: `Bearer ${API_KEY}` } : undefined,
      });
    } catch (err) {
      lastErr = err;
      logLine(`⚠️ POST attempt ${i} failed:`, err.response?.status || err.code);
      if (i < tries) await new Promise((r) => setTimeout(r, 500 * i));
    }
  }
  throw lastErr;
}

// ============================================================================
// MQTT INIT
// ============================================================================
let client;
let mqttReadyResolve;
const mqttReady = new Promise((res) => (mqttReadyResolve = res));

function initMqtt() {
  client = mqtt.connect(MQTT_SERVER, {
    clientId: "mqtt-publisher-" + Math.random().toString(16).slice(2, 10),
    clean: true,
    reconnectPeriod: 1500,
    connectTimeout: 6000,
  });

  client.on("connect", () => {
    logLine("✅ MQTT connected:", MQTT_SERVER);
    mqttReadyResolve();
  });
  client.on("reconnect", () => logLine("… MQTT reconnecting"));
  client.on("error", (e) => logLine("❌ MQTT ERROR:", e.message));
}

function mqttPublish(topic, message, options = { qos: 1, retain: false }) {
  return new Promise((resolve, reject) =>
    client.publish(topic, message, options, (err) =>
      err ? reject(err) : resolve()
    )
  );
}

// ============================================================================
// FILE PROCESSING
// ============================================================================
const BACKGROUND_RX = /_background/i;

function mapTokens(tokens) {
  return {
    timestamp: tokens[0] || null,
    vehicle_id: tokens[1] || null,
    event_category: tokens[2] || null,
    event_type: tokens[3] || null,
    camera_code: tokens[4] || null,
    direction: tokens[5] || null,
    lane: tokens[6] || null,
  };
}

// ============================================================================
// 1-MINUTE DEDUPLICATION (AVOID DUPLICATE PUSHES)
// ============================================================================
const DEDUP_WINDOW_MS = 5_000; // 1 minute (60_000 1 minute)
const recentEvents = new Map(); // key -> lastProcessedTimestamp (ms)

// Build a "logical event" key from parsed fields
function makeDedupKey(fields) {
  // Adjust fields used for dedup as per your business logic
  return [
    fields.vehicle_id || "",
    fields.event_category || "",
    fields.event_type || "",
    fields.camera_code || "",
    fields.direction || "",
    fields.lane || "",
  ].join("|");
}

function isDuplicateWithinWindow(fields) {
  const now = Date.now();
  const key = makeDedupKey(fields);

  const lastTs = recentEvents.get(key);
  if (lastTs && now - lastTs < DEDUP_WINDOW_MS) {
    // Duplicate within the last minute
    return true;
  }

  // Not a duplicate (or older than window) → update cache
  recentEvents.set(key, now);

  // Optional: lightweight cleanup of very old entries
  for (const [k, ts] of recentEvents.entries()) {
    if (now - ts > DEDUP_WINDOW_MS * 5) {
      recentEvents.delete(k);
    }
  }

  return false;
}

// ============================================================================
// HANDLE SINGLE FILE (CALLED BY QUEUE)
// ============================================================================
async function handleBackgroundFile(filePath) {
  const name = path.basename(filePath);

  if (!BACKGROUND_RX.test(name)) return;
  if (processed.has(name)) {
    logLine("↩️ Already processed, skipping:", name);
    return;
  }

  logLine("📥 New file:", name);

  if (!(await waitForStableFile(filePath))) {
    logLine("⚠️ Not stable, skipped:", name);
    return;
  }

  const base = path.basename(name, path.extname(name));
  const tokens = base.split("_").filter(Boolean);
  const fields = mapTokens(tokens);

  // 1-minute duplicate suppression (logical event level)
  if (isDuplicateWithinWindow(fields)) {
    logLine("⏱️ Duplicate event within 1 minute, skipping push:", {
      vehicle_id: fields.vehicle_id,
      event_category: fields.event_category,
      event_type: fields.event_type,
      camera_code: fields.camera_code,
      direction: fields.direction,
      lane: fields.lane,
    });
    // Mark file as processed so we don't re-read it after restart
    markAsProcessed(name);
    return;
  }

  const payload = {
    filename: name,
    company_id: COMPANY_ID,
    ...fields,
    ext: path.extname(name).replace(".", ""),
  };

  try {
    const res = await postWithRetry(payload, 1);
    logLine("✅ API OK:", res.status);

    await mqttReady;
    await mqttPublish(TOPIC_EVENT, JSON.stringify({ response: res.data }));
    logLine("📤 MQTT published:", TOPIC_EVENT);

    // Persist this as processed (file-level)
    markAsProcessed(name);
  } catch (err) {
    logLine("❌ POST ERROR:", err.response?.data || err.message);
  }
}

// ============================================================================
// SIMPLE QUEUE → ENSURES "ONE BY ONE" PROCESSING
// ============================================================================
let fileQueue = [];
let isProcessingQueue = false;

function enqueueFile(filePath) {
  fileQueue.push(filePath);
  processQueue();
}

async function processQueue() {
  if (isProcessingQueue) return;
  isProcessingQueue = true;

  while (fileQueue.length > 0) {
    const filePath = fileQueue.shift();
    try {
      await handleBackgroundFile(filePath);
    } catch (err) {
      logLine("❌ Error in queue processor:", err.message);
    }
  }

  isProcessingQueue = false;
}

// ============================================================================
// SCAN EXISTING FILES ON STARTUP (BACKLOG)
// ============================================================================
async function scanExistingFiles() {
  try {
    const files = await fsp.readdir(WATCH_DIR);
    logLine(`🔍 Scanning existing files in WATCH_DIR (${files.length} found).`);

    // Only relevant + not already processed
    const backlog = files
      .filter((name) => BACKGROUND_RX.test(name) && !processed.has(name))
      .sort(); // sort for deterministic order (optional)

    logLine(`📌 Backlog files to enqueue: ${backlog.length}`);

    for (const name of backlog) {
      const filePath = path.join(WATCH_DIR, name);
      enqueueFile(filePath);
    }

    logLine("✅ Initial backlog enqueue complete.");
  } catch (err) {
    logLine("❌ Error during initial scan:", err.message);
  }
}

// ============================================================================
// START WATCHING
// ============================================================================
async function startWatcher() {
  await loadConfig();
  initMqtt();

  if (!fs.existsSync(WATCH_DIR)) {
    logLine("❌ WATCH_DIR missing:", WATCH_DIR);
    process.exit(1);
  }

  // Now that COMPANY_ID is known, set the processed store path
  processedStorePath = path.join(logDir, `processed_files_${COMPANY_ID}.json`);

  await loadProcessedFromDisk();
  await scanExistingFiles(); // handle backlog on restart (enqueued, then processed one by one)

  logLine("👀 Watching:", WATCH_DIR);
  logLine("→ API:", API_URL);
  logLine("→ MQTT:", MQTT_SERVER);
  logLine("→ Topic:", TOPIC_EVENT);

  chokidar
    .watch(WATCH_DIR, {
      ignoreInitial: true,
      persistent: true,
      depth: 0,
      usePolling: false,
      ignorePermissionErrors: true,
    })
    .on("add", (filePath) => {
      // Just enqueue, queue ensures one-by-one
      enqueueFile(filePath);
    })
    .on("error", (e) => logLine("❌ Watcher ERROR:", e.message));

  // Graceful exit
  async function gracefulExit(signal) {
    logLine(`${signal} received. Exiting...`);
    try {
      // Force a final save of processed store
      if (processedStorePath) {
        const arr = Array.from(processed);
        await fsp.writeFile(processedStorePath, JSON.stringify(arr), "utf8");
        logLine(`💾 Final save of processed-file store (${arr.length} files).`);
      }
    } catch (err) {
      logLine("❌ Error during final save:", err.message);
    }

    client?.end(true);
    process.exit(0);
  }

  process.on("SIGINT", () => {
    gracefulExit("SIGINT");
  });
  process.on("SIGTERM", () => {
    gracefulExit("SIGTERM");
  });
}

// ============================================================================
// START
// ============================================================================
startWatcher();
