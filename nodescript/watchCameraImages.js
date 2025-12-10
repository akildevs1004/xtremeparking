#!/usr/bin/env node
// Watch *_BACKGROUND files; parse tokens; POST to API; publish MQTT, log daily.

const mqtt = require("mqtt");
const chokidar = require("chokidar");
const path = require("path");
const fs = require("fs");
const fsp = fs.promises;
const axios = require("axios");
require("dotenv").config();

// ---- ENV ----
const COMPANY_ID = process.env.COMPANY_ID;
const WATCH_DIR =
  (process.env.WATCH_DIR || "./inbox") + (COMPANY_ID ? `/${COMPANY_ID}` : "");
const API_URL = process.env.API_URL;
const API_KEY = process.env.API_KEY || "";
const MQTT_SERVER = process.env.MQTT_SERVER;

if (!COMPANY_ID || !API_URL || !MQTT_SERVER) {
  console.error(
    "❌ Missing required env. Need COMPANY_ID, API_URL, MQTT_SERVER"
  );
  process.exit(1);
}
if (!fs.existsSync(WATCH_DIR)) {
  console.error("❌ Folder does not exist:", path.resolve(WATCH_DIR));
  process.exit(1);
}

// ---- Logging ----
const logDir = path.resolve(__dirname, "logs");
if (!fs.existsSync(logDir)) {
  fs.mkdirSync(logDir, { recursive: true });
}
const logFile = path.join(
  logDir,
  `${new Date().toISOString().slice(0, 10)}.log`
);
function logLine(...args) {
  // const msg = `[${new Date().toISOString()}] ${args.join(" ")}\n`;
  // fs.appendFileSync(logFile, msg);
  // console.log(...args); // still print to console

  const timestamp = new Date().toISOString();
  const text = args
    .map((a) => (typeof a === "object" ? JSON.stringify(a) : a))
    .join(" ");

  const msg = `[${timestamp}] ${text}\n`;

  try {
    // safe async append prevents blocking supervisor log tail
    fs.appendFile(logFile, msg, (err) => {
      if (err) {
        console.error("LOG WRITE ERROR:", err);
      }
    });
  } catch (err) {
    console.error("LOG ERROR:", err);
  }

  // console output stays clean
  console.log(text);
}

// ---- Helpers ----
const topicBase = `xtremeparking/${COMPANY_ID}/cameralogs`;
const topicNewEvent = `${topicBase}/new_event`;
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
    tag: tokens[7] || null,
  };
}

// Wait until file size is stable
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

// Axios client
const http = axios.create({
  baseURL: API_URL,
  timeout: 30000,
  headers: API_KEY ? { Authorization: `Bearer ${API_KEY}` } : undefined,
});

// Retry POST
async function postWithRetry(url, payload, tries = 1) {
  let lastErr;
  for (let i = 1; i <= tries; i++) {
    try {
      return await http.post("", payload);
    } catch (err) {
      lastErr = err;
      const code = err.response?.status || err.code || "ERR";
      logLine(`⚠️ POST attempt ${i} failed (${code}).`);
      if (i < tries) await new Promise((r) => setTimeout(r, 500 * i));
    }
  }
  throw lastErr;
}

// ---- MQTT (persistent) ----
let mqttReadyResolve;
const mqttReady = new Promise((res) => (mqttReadyResolve = res));
const client = mqtt.connect(MQTT_SERVER, {
  clientId: `node-mqtt-publisher-${Math.random().toString(16).slice(2, 10)}`,
  clean: true,
  connectTimeout: 6000,
  reconnectPeriod: 1500,
});

client.on("connect", () => {
  logLine("✅ MQTT connected:", MQTT_SERVER);
  mqttReadyResolve();
});
client.on("reconnect", () => logLine("… MQTT reconnecting"));
client.on("error", (e) => logLine("❌ MQTT error:", e.message));

function mqttPublish(topic, message, options = { qos: 1, retain: false }) {
  return new Promise((resolve, reject) => {
    client.publish(topic, message, options, (err) =>
      err ? reject(err) : resolve()
    );
  });
}

// ---- Main watcher ----
logLine("👀 Watching:", path.resolve(WATCH_DIR));
logLine("→ API:", API_URL);
logLine("→ MQTT topic:", topicNewEvent);

const processed = new Set();

chokidar
  .watch(WATCH_DIR, {
    ignoreInitial: true, // don’t trigger on startup
    persistent: true, // keep process alive
    depth: 0, // only top-level dir
    usePolling: false, // use native fs events (much faster!)
    ignorePermissionErrors: true,
    // ignoreInitial: true,
    // persistent: true,
    // depth: 0,
    // usePolling: true,
    // interval: 200,
    // binaryInterval: 1500,
    // ignorePermissionErrors: true,
    // awaitWriteFinish: { stabilityThreshold: 500, pollInterval: 200 },
    // ignoreInitial: true,
    // persistent: true,
    // depth: 0,
    // usePolling: true,
    // interval: 1000,
    // binaryInterval: 1500,
    // ignorePermissionErrors: true,
    // awaitWriteFinish: { stabilityThreshold: 1500, pollInterval: 200 },
    // ignored: (p) => {
    //   const name = path.basename(p);
    //   if (path.dirname(p) !== path.resolve(WATCH_DIR)) return true;
    //   return !BACKGROUND_RX.test(name);
    // },
  })
  .on("add", async (filePath) => {
    const name = path.basename(filePath);
    const timestamp = new Date().toLocaleString();

    logLine(timestamp + ": 📥 New File added:", name, BACKGROUND_RX.test(name));

    if (!BACKGROUND_RX.test(name)) return;

    if (processed.has(name)) return;

    try {
      const stable = await waitForStableFile(filePath);
      if (!stable) {
        logLine("⚠️ File not stable, skipping:", name);
        return;
      }

      const ext = (path.extname(name) || "").slice(1);
      const base = path.basename(name, path.extname(name));
      const tokens = base.split("_").filter(Boolean);
      const fields = mapTokens(tokens);

      const payload = {
        filename: name,
        company_id: COMPANY_ID,

        timestamp: fields.timestamp,
        vehicle_id: fields.vehicle_id,
        event_category: fields.event_category,
        event_type: fields.event_type,
        camera_code: fields.camera_code,
        direction: fields.direction,
        lane: fields.lane,

        fields,
        ext,
      };

      logLine("⇢ Payload:", JSON.stringify(payload));

      const res = await postWithRetry(API_URL, payload, 1);
      logLine("✅ API OK:", res.status, name);

      await mqttReady;
      const mqttData = {
        device: "ParkingCamera1",
        response: res.data ?? null,
      };
      await mqttPublish(topicNewEvent, JSON.stringify(mqttData), {
        qos: 1,
        retain: false,
      });
      logLine("📤 MQTT published:", topicNewEvent);
      processed.add(name);
    } catch (err) {
      const code = err.response?.status || err.code || "ERR";
      const body = err?.response?.data
        ? JSON.stringify(err.response.data)
        : err.message || "";
      logLine("❌ [POST ERROR]", name, code, body);
    }
  })
  .on("error", (e) => logLine("❌ [WATCH ERROR]", e.message));

// ---- Graceful shutdown ----
function shutdown(sig) {
  logLine(`${sig} received. Closing…`);
  try {
    client.end(true);
  } catch {}
  process.exit(0);
}
["SIGINT", "SIGTERM"].forEach((s) => process.on(s, () => shutdown(s)));
process.on("unhandledRejection", (r) =>
  logLine("[unhandledRejection]", r?.message || r)
);
process.on("uncaughtException", (e) =>
  logLine("[uncaughtException]", e.message)
);
