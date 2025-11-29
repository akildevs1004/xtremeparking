#!/usr/bin/env node
// Watch folder continuously; on *_BACKGROUND file added:
// - split filename into tokens
// - map tokens into fields
// - POST { filename, path, tokens, fields, ext } to API

const mqtt = require("mqtt");

// --- MQTT Broker details ---

const chokidar = require("chokidar");
const path = require("path");
const fs = require("fs");
const axios = require("axios");
require("dotenv").config();

const WATCH_DIR = process.env.WATCH_DIR + "/" + process.env.COMPANY_ID; // || "./inbox";
const API_URL = process.env.API_URL;
const API_KEY = process.env.API_KEY;

const host = process.env.MQTT_SERVER; //|| "mqtt://broker.hivemq.com"; // Replace with your broker (mqtt://ip:1883)
const topic = "xtremeparking/" + process.env.COMPANY_ID + "/cameralogs";
const newEvent = topic + "/new_event";

const BACKGROUND_RX = /_background/i;
/////--------------------MQTT START

// --- Connect to broker ---
const client = mqtt.connect(host, {
  clientId: "node-mqtt-publisher-" + Math.random().toString(16).substr(2, 8),
  clean: true,
  connectTimeout: 4000,
  username: "", // optional
  password: "", // optional
  reconnectPeriod: 1000,
});
client.on("connect", () => {
  console.log("✅ Connected to MQTT broker");
});

client.on("error", (err) => {
  console.error("Connection error:", err);
  client.end();
});
/////--------------------MQTT END

if (!fs.existsSync(WATCH_DIR)) {
  console.error("Folder does not exist:", path.resolve(WATCH_DIR));
  process.exit(1);
}

// const headers = { "Content-Type": "application/json" };
// if (API_KEY) headers.Authorization = `Bearer ${API_KEY}`;

console.log("Watching for *_BACKGROUND files in:", path.resolve(WATCH_DIR));
console.log("POST →", API_URL);

// ---- helper to map tokens into fields ----
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

chokidar
  .watch(WATCH_DIR, {
    ignoreInitial: true,
    persistent: true,
    depth: 0,
    usePolling: true,
    interval: 1000,
    binaryInterval: 1500,
    ignorePermissionErrors: true,
    awaitWriteFinish: {
      stabilityThreshold: 1500,
      pollInterval: 200,
    },
  })
  .on("add", async (filePath) => {
    const name = path.basename(filePath);
    if (!BACKGROUND_RX.test(name)) return;

    const ext = (path.extname(name) || "").slice(1);
    const base = path.basename(name, path.extname(name));
    const tokens = base.split("_").filter(Boolean);
    const fields = mapTokens(tokens);

    const payload = {
      filename: name,
      company_id: process.env.COMPANY_ID,

      timestamp: fields.timestamp,
      //   path: path.resolve(filePath),
      //   tokens,
      fields,
      ext,
    };

    console.log(payload);

    try {
      const res = await axios.post(API_URL, payload, {
        // headers,
        timeout: 30000,
      });
      console.log("[API SENT]", name, res.data ?? "");

      // MQTT data
      const data = {
        device: "ParkingCamera1",
        response: res.data,
      };

      // Publish JSON data
      await client.publish(
        newEvent,
        JSON.stringify(data),
        { qos: 0, retain: false },
        (error) => {
          if (error) {
            console.error("Publish error:", error);
          } else {
            console.log("📤 Data published MQTT:", data);
            client.end(); // close connection after publish
          }
        }
      ); //MQTT
    } catch (err) {
      const code = err.response?.status || err.code || "";
      console.error(
        "[POST ERROR]",
        name,
        code,

        JSON.stringify(err?.response?.data) || err.message
      );
    }
  })
  .on("error", (e) => console.error("[WATCH ERROR]", e.message));

process.on("unhandledRejection", (r) =>
  console.error("[unhandledRejection]", r?.message || r)
);
process.on("uncaughtException", (e) =>
  console.error("[uncaughtException]", e.message)
);
