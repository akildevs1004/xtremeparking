// server_stream.js
// Node server: receives MPEG-TS from FFmpeg and streams to JSMpeg via WebSocket

const http = require("http");
const WebSocket = require("ws");
const axios = require("axios");
const { spawn } = require("child_process");

// ========= CONFIG =========
const CAMERA_API_URL =
  "http://127.0.0.1:8000/api/parking-cameras?company_id=8&login_user_id=1875&login_user_type=company";
// same as in Laravel

const BASE_HTTP_PORT = 8081; // must match BAT file
const BASE_WS_PORT = 9991; // WebSocket ports for browsers

const FFMPEG_BAT = "start_ffmpeg_from_api.bat"; // same folder

// ========= START BAT FILE (FFmpeg for all cameras) =========
function startFfmpegBat() {
  console.log("Starting FFmpeg BAT file:", FFMPEG_BAT);

  const child = spawn("cmd.exe", ["/c", FFMPEG_BAT], {
    cwd: __dirname,
    detached: true,
    shell: true,
  });

  child.on("error", (err) => {
    console.error("Failed to start FFmpeg BAT:", err);
  });

  child.stdout?.on("data", (data) => {
    console.log(`BAT: ${data}`);
  });

  child.stderr?.on("data", (data) => {
    console.error(`BAT ERROR: ${data}`);
  });

  child.on("close", (code) => {
    console.log(`BAT exited with code ${code}`);
  });

  console.log("FFmpeg BAT launched (FFmpeg processes will run independently).");
}

// ========= FETCH CAMERAS FROM LARAVEL =========
async function fetchCameras() {
  console.log("Fetching cameras from Laravel:", CAMERA_API_URL);
  const res = await axios.get(CAMERA_API_URL, { timeout: 10000 });
  const list = res.data && res.data.data ? res.data.data : [];

  if (!Array.isArray(list) || list.length === 0) {
    throw new Error("No cameras returned from Laravel API.");
  }

  console.log(`Received ${list.length} cameras:`);
  list.forEach((c) => console.log(` - [${c.id}] ${c.name} -> ${c.rtsp_url}`));

  return list;
}

// ========= SETUP SERVERS FOR EACH CAMERA =========
async function main() {
  // 1) Start FFmpeg via BAT
  startFfmpegBat();

  // 2) Fetch cameras
  const cameras = await fetchCameras();

  // 3) Create HTTP + WS servers for each camera (same index mapping as BAT)
  cameras.forEach((camCfg, index) => {
    const httpPort = BASE_HTTP_PORT + index;
    const wsPort = BASE_WS_PORT + index;

    const cam = {
      id: camCfg.id,
      name: camCfg.name || `Camera ${index + 1}`,
      httpPort,
      wsPort,
    };

    console.log("\n====================================");
    console.log(`Camera #${index + 1}`);
    console.log(`ID       : ${cam.id}`);
    console.log(`Name     : ${cam.name}`);
    console.log(`FFMPEG → : http://127.0.0.1:${cam.httpPort}/stream`);
    console.log(`WS OUT   : ws://YOUR_NODE_PC_IP:${cam.wsPort}`);
    console.log("====================================\n");

    // --- WebSocket server for JSMpeg clients ---
    const wsServer = new WebSocket.Server({
      port: cam.wsPort,
      perMessageDeflate: false,
    });

    wsServer.on("connection", (socket) => {
      console.log(
        `[CAM ${cam.id}] Viewer connected on WS ${cam.wsPort}. Total: ${wsServer.clients.size}`
      );
      socket.on("close", () => {
        console.log(
          `[CAM ${cam.id}] Viewer disconnected. Total: ${wsServer.clients.size}`
        );
      });
    });

    // --- HTTP server to receive MPEG-TS from FFmpeg ---
    const streamServer = http.createServer((req, res) => {
      console.log(
        `[CAM ${cam.id}] Incoming MPEG-TS from FFmpeg on port ${cam.httpPort}`
      );

      req.on("data", (chunk) => {
        wsServer.clients.forEach((client) => {
          if (client.readyState === WebSocket.OPEN) {
            client.send(chunk);
          }
        });
      });

      req.on("end", () => {
        console.log(`[CAM ${cam.id}] HTTP stream ended.`);
        res.end();
      });

      req.socket.setTimeout(0); // keep alive
    });

    streamServer.listen(cam.httpPort, () => {
      console.log(
        `[CAM ${cam.id}] HTTP listening on ${cam.httpPort} (FFmpeg target).`
      );
    });
  });

  console.log(
    "\nAll camera servers initialized. Keep this Node process running."
  );
}

main().catch((err) => {
  console.error("Fatal error in server_stream.js:", err.message);
});
