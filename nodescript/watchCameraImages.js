#!/usr/bin/env node

// Watch *_BACKGROUND files → parse → POST to API → publish MQTT → log daily

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
// LOAD ALL CONFIG FROM API
// ============================================================================
async function loadConfig() {
  try {
    logLine("🔄 Loading ALL environment variables from API:", CONFIG_ENDPOINT);

    const res = await axios.get(CONFIG_ENDPOINT, { timeout: 5000 });

    const cfg = res.data || {};

    // MAP EXACT JSON KEYS
    COMPANY_ID = cfg.COMPANY_ID?.toString().trim() ?? "";
    WATCH_DIR = cfg.WATCH_DIR?.toString().trim() + "/" + COMPANY_ID ?? "";
    MQTT_SERVER =
      cfg.MQTT_SERVER?.toString().trim() || cfg.host?.toString().trim() || "";
    API_URL = cfg.API_URL?.toString().trim() ?? "";
    API_KEY = cfg.API_KEY?.toString().trim() ?? "";

    logLine("✅ Config loaded from API");
  } catch (err) {
    logLine("❌ Failed API config load. Using .env fallback.", err.message);

    COMPANY_ID = process.env.COMPANY_ID ?? "";
    WATCH_DIR = process.env.WATCH_DIR ?? "./inbox";
    MQTT_SERVER = process.env.MQTT_SERVER ?? "";
    API_URL = process.env.API_URL ?? "";
    API_KEY = process.env.API_KEY ?? "";
  }

  // Final validation
  if (!COMPANY_ID || !WATCH_DIR || !MQTT_SERVER || !API_URL) {
    logLine("❌ Missing required configuration after API + fallback.");
    logLine({ COMPANY_ID, WATCH_DIR, MQTT_SERVER, API_URL });
    process.exit(1);
  }

  logLine("COMPANY_ID :", COMPANY_ID);
  logLine("WATCH_DIR  :", WATCH_DIR);
  logLine("MQTT_SERVER:", MQTT_SERVER);
  logLine("API_URL    :", API_URL);
}

// ============================================================================
// STABILITY CHECK FOR FILES
// ============================================================================
async function waitForStableFile(filePath, tries = 5, delayMs = 400) {
  let prev = -1;
  for (let i = 0; i < tries; i++) {
    const { size } = await fsp.stat(filePath);
    if (size === prev) return true;
    prev = size;
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
// START WATCHING
// ============================================================================
async function startWatcher() {
  await loadConfig();
  initMqtt();

  if (!fs.existsSync(WATCH_DIR)) {
    logLine("❌ WATCH_DIR missing:", WATCH_DIR);
    process.exit(1);
  }

  const topicEvent = `xtremeparking/${COMPANY_ID}/cameralogs/new_event`;

  logLine("👀 Watching:", WATCH_DIR);
  logLine("→ API:", API_URL);
  logLine("→ MQTT:", MQTT_SERVER);
  logLine("→ Topic:", topicEvent);

  const processed = new Set();

  chokidar
    .watch(WATCH_DIR, {
      ignoreInitial: true,
      persistent: true,
      depth: 0,
      usePolling: false,
      ignorePermissionErrors: true,
    })
    .on("add", async (filePath) => {
      const name = path.basename(filePath);

      if (!BACKGROUND_RX.test(name)) return;
      if (processed.has(name)) return;

      logLine("📥 New file:", name);

      if (!(await waitForStableFile(filePath))) {
        logLine("⚠️ Not stable, skipped:", name);
        return;
      }

      const base = path.basename(name, path.extname(name));
      const tokens = base.split("_").filter(Boolean);
      const fields = mapTokens(tokens);

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
        await mqttPublish(topicEvent, JSON.stringify({ response: res.data }));
        logLine("📤 MQTT published:", topicEvent);

        processed.add(name);
      } catch (err) {
        logLine("❌ POST ERROR:", err.response?.data || err.message);
      }
    })
    .on("error", (e) => logLine("❌ Watcher ERROR:", e.message));

  // Graceful exit
  process.on("SIGINT", () => {
    logLine("SIGINT received. Exiting...");
    client?.end(true);
    process.exit(0);
  });
  process.on("SIGTERM", () => {
    logLine("SIGTERM received. Exiting...");
    client?.end(true);
    process.exit(0);
  });
}

// ============================================================================
// START
// ============================================================================
startWatcher();
