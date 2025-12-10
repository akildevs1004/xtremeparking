// XtremeParking - Unified Camera Live Stream Server
// Renamed from server_stream.js → start_camera_live_stream.js
// ------------------------------------------------------------------
// - Fetch cameras from Laravel API
// - Start per-camera HTTP MPEG-TS ingest server
// - Start per-camera WebSocket server for JSMpeg
// - Spawn FFmpeg per camera (RTSP → MPEG-TS → WebSocket)
// ------------------------------------------------------------------

const http = require("http");
const WebSocket = require("ws");
const axios = require("axios");
const { spawn } = require("child_process");

// ========= CONFIG =========

// Laravel API that returns array of { id, name, rtsp_url }
const CAMERA_API_URL =
  "http://127.0.0.1:8000/api/parking-cameras?company_id=8&login_user_id=1875&login_user_type=company";

// Ports
const BASE_HTTP_PORT = 7081; // FFmpeg pushes MPEG-TS here
const BASE_WS_PORT = 9991; // Browser streams from here

// FFmpeg settings
const FFMPEG_PATH = "ffmpeg";
const STREAM_WIDTH = 1280;
const STREAM_HEIGHT = 720;
const STREAM_FPS = 25;
const STREAM_BITRATE = "2000k";

const DEBUG_FFMPEG = false;

// Store FFmpeg child processes
const ffmpegProcesses = [];

// -----------------------------------------------
// FETCH CAMERAS FROM LARAVEL
// -----------------------------------------------
async function fetchCameras() {
  console.log("Fetching cameras from Laravel:", CAMERA_API_URL);

  const response = await axios.get(CAMERA_API_URL, { timeout: 10000 });
  const cameras = response.data?.data || [];

  if (!Array.isArray(cameras) || cameras.length === 0) {
    throw new Error("Laravel API returned no cameras.");
  }

  console.log(`Received ${cameras.length} cameras:\n`);
  cameras.forEach((cam) => {
    console.log(` - [${cam.id}] ${cam.name} → ${cam.rtsp_url}`);
  });

  return cameras;
}

// -----------------------------------------------
// START FFMPEG PROCESS FOR A CAMERA
// -----------------------------------------------
function startFFmpeg(cam, httpPort) {
  const pushUrl = `http://127.0.0.1:${httpPort}/stream`;

  console.log(
    `[FFMPEG CAM ${cam.id}] Starting → ${pushUrl} (${STREAM_WIDTH}x${STREAM_HEIGHT} @ ${STREAM_FPS}fps)`
  );

  const args = [
    "-rtsp_transport",
    "tcp",
    "-i",
    cam.rtsp_url,
    "-f",
    "mpegts",
    "-codec:v",
    "mpeg1video",
    "-s",
    `${STREAM_WIDTH}x${STREAM_HEIGHT}`,
    "-b:v",
    STREAM_BITRATE,
    "-bf",
    "0",
    "-r",
    `${STREAM_FPS}`,
    pushUrl,
  ];

  const ffmpeg = spawn(FFMPEG_PATH, args, {
    stdio: DEBUG_FFMPEG ? "inherit" : "ignore",
  });

  ffmpegProcesses.push(ffmpeg);

  ffmpeg.on("error", (err) => {
    console.error(`[FFMPEG CAM ${cam.id}] Failed:`, err);
  });

  ffmpeg.on("close", (code) => {
    console.log(`[FFMPEG CAM ${cam.id}] Exit code: ${code}`);
  });
}

// -----------------------------------------------
// START SERVERS PER CAMERA
// -----------------------------------------------
function startCameraServers(cam, index) {
  const httpPort = BASE_HTTP_PORT + index;
  const wsPort = BASE_WS_PORT + index;

  console.log("\n====================================");
  console.log(`Camera #${index + 1}`);
  console.log(`ID       : ${cam.id}`);
  console.log(`Name     : ${cam.name}`);
  console.log(`RTSP     : ${cam.rtsp_url}`);
  console.log(`FFMPEG → : http://127.0.0.1:${httpPort}/stream`);
  console.log(`WS OUT   : ws://YOUR_NODE_PC_IP:${wsPort}`);
  console.log("====================================\n");

  // Create WebSocket server
  let wsServer;
  try {
    wsServer = new WebSocket.Server({
      port: wsPort,
      perMessageDeflate: false,
    });
  } catch (error) {
    console.error(`[ERROR] Could not open WS port ${wsPort}. Already in use?`);
    process.exit(1);
  }

  wsServer.on("connection", () => {
    console.log(
      `[CAM ${cam.id}] Viewer connected (${wsServer.clients.size} total)`
    );
  });

  // Create HTTP ingest server
  const httpServer = http.createServer((req, res) => {
    req.on("data", (chunk) => {
      wsServer.clients.forEach((client) => {
        if (client.readyState === WebSocket.OPEN) client.send(chunk);
      });
    });

    req.on("end", () => res.end());
    req.socket.setTimeout(0); // prevent timeout
  });

  httpServer.listen(httpPort, () => {
    console.log(`[CAM ${cam.id}] HTTP ingest listening on port ${httpPort}`);
  });

  startFFmpeg(cam, httpPort);
}

// -----------------------------------------------
// MAIN EXECUTION
// -----------------------------------------------
async function main() {
  const cameras = await fetchCameras();

  cameras.forEach((cam, index) => startCameraServers(cam, index));

  console.log("\nAll camera live stream servers started.\n");
}

main().catch((err) => {
  console.error("Fatal Error:", err.message);
});
