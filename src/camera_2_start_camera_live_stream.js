module.exports.start = function start({ logger, beat, appDir }) {
  logger(`camera_2_start_camera_live_stream started. appDir=${appDir}`);

  // Heartbeat every 5 seconds so you KNOW it is alive
  setInterval(() => {
    beat("alive");
  }, 5000);

  try {
    // put your existing code here
    // XtremeParking - Unified Camera Live Stream Server
    // ------------------------------------------------------------------
    // - Fetch cameras from Laravel API
    // - Start per-camera HTTP MPEG-TS ingest server
    // - Start per-camera WebSocket server for JSMpeg
    // - Spawn FFmpeg per camera (RTSP → MPEG-TS → WebSocket)
    // - BASE_HTTP_PORT / BASE_WS_PORT loaded from /api/get_mqtt_server envsettings
    // - Node stays up; FFmpeg auto-restarts on failure
    // - Camera Health JSON API on HEALTH_PORT (/health)
    // ------------------------------------------------------------------

    const http = require("http");
    const WebSocket = require("ws");
    const axios = require("axios");
    const { spawn } = require("child_process");
    const path = require("path"); // ✅ added

    // ========= CONFIG =========

    // Laravel API that returns array of { id, name, rtsp_url }
    const CAMERA_API_URL =
      "http://127.0.0.1:8000/api/parking-cameras?company_id=8&login_user_type=company";

    // Config API to fetch ONLY BASE_HTTP_PORT and BASE_WS_PORT
    const CONFIG_API_URL = "http://127.0.0.1:8000/api/envsettings";

    // Default ports (will be overridden by CONFIG_API_URL if present)
    let BASE_HTTP_PORT = 7081; // FFmpeg pushes MPEG-TS here
    let BASE_WS_PORT = 9991; // Browser streams from here

    // Health API port (fixed; change if needed)
    const HEALTH_PORT = 7090;

    // FFmpeg settings
    // const FFMPEG_PATH = "ffmpeg";
    const FFMPEG_PATH = path.join(appDir, "ffmpeg", "ffmpeg.exe"); // ✅ use local ffmpeg
    const STREAM_WIDTH = 1280;
    const STREAM_HEIGHT = 720;
    const STREAM_FPS = 25;
    const STREAM_BITRATE = "2000k";

    // Debug FFmpeg logs
    const DEBUG_FFMPEG = false;

    // WS backpressure guard: if a client is too slow and its send buffer exceeds
    // this, we skip frames for that client to protect memory.
    const MAX_WS_BUFFERED_AMOUNT = 8 * 1024 * 1024; // 8 MB

    // Store FFmpeg child processes (optional, for later management)
    const ffmpegProcesses = [];

    // Camera health state (in-memory)
    const cameraHealth = {}; // key: cam.id → status object

    // Helper to get timestamp string
    function nowIso() {
      return new Date().toISOString();
    }

    // -----------------------------------------------
    // LOAD PORTS FROM CONFIG API
    // -----------------------------------------------
    async function loadPortsFromApi() {
      try {
        console.log("Loading ports from:", CONFIG_API_URL);
        const res = await axios.get(CONFIG_API_URL, { timeout: 5000 });

        // Support:
        //  { BASE_HTTP_PORT: 7081, BASE_WS_PORT: 9991 }
        //  { data: { BASE_HTTP_PORT: 7081, BASE_WS_PORT: 9991, ... } }
        const cfgRaw = res.data || {};
        const cfg =
          cfgRaw.data && typeof cfgRaw.data === "object" ? cfgRaw.data : cfgRaw;

        if (cfg.BASE_HTTP_PORT !== undefined) {
          const p = Number(cfg.BASE_HTTP_PORT);
          if (!Number.isNaN(p) && p > 0 && p < 65536) {
            BASE_HTTP_PORT = p;
          }
        }

        if (cfg.BASE_WS_PORT !== undefined) {
          const p = Number(cfg.BASE_WS_PORT);
          if (!Number.isNaN(p) && p > 0 && p < 65536) {
            BASE_WS_PORT = p;
          }
        }

        console.log(
          `Using ports → BASE_HTTP_PORT=${BASE_HTTP_PORT}, BASE_WS_PORT=${BASE_WS_PORT}`,
        );
      } catch (e) {
        console.error(
          "⚠️ Failed to load ports from API. Using default ports.",
          e.message,
        );
        console.log(
          `BASE_HTTP_PORT=${BASE_HTTP_PORT}, BASE_WS_PORT=${BASE_WS_PORT}`,
        );
      }
    }

    // -----------------------------------------------
    // FETCH CAMERAS FROM LARAVEL
    // -----------------------------------------------
    async function fetchCameras() {
      console.log("Fetching cameras from Laravel:", CAMERA_API_URL);

      const response = await axios.get(CAMERA_API_URL, { timeout: 10000 });
      const cameras = response?.data || [];

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
    // UPDATE HEALTH HELPERS
    // -----------------------------------------------
    function initCameraHealth(cam, httpPort, wsPort) {
      cameraHealth[cam.id] = {
        id: cam.id,
        name: cam.name,
        rtsp_url: cam.rtsp_url,
        httpPort,
        wsPort,
        status: "initializing", // initializing | running | restarting | stopped | error
        viewers: 0,
        lastStartAt: null,
        lastStopAt: null,
        lastExitCode: null,
        lastExitSignal: null,
        lastError: null,
      };
    }

    function setCameraStatus(camId, patch) {
      if (!cameraHealth[camId]) return;
      Object.assign(cameraHealth[camId], patch);
    }

    // -----------------------------------------------
    // START FFMPEG PROCESS FOR A CAMERA (AUTO-RESTART)
    // -----------------------------------------------
    function startFFmpeg(cam, httpPort) {
      const pushUrl = `http://127.0.0.1:${httpPort}/stream`;

      setCameraStatus(cam.id, {
        status: "running",
        lastStartAt: nowIso(),
        lastError: null,
      });

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
        console.error(`[FFMPEG CAM ${cam.id}] Failed to start:`, err);
        setCameraStatus(cam.id, {
          status: "error",
          lastError: String(err),
        });
      });

      ffmpeg.on("close", (code, signal) => {
        console.log(
          `[FFMPEG CAM ${cam.id}] Exit code: ${code}, signal: ${signal}`,
        );

        setCameraStatus(cam.id, {
          status: "stopped",
          lastStopAt: nowIso(),
          lastExitCode: code,
          lastExitSignal: signal,
        });

        if (code !== 0) {
          setCameraStatus(cam.id, { status: "restarting" });
          setTimeout(() => startFFmpeg(cam, httpPort), 5000);
        } else {
          console.log(`[FFMPEG CAM ${cam.id}] Clean exit (code=0).`);
        }
      });
    }

    // -----------------------------------------------
    // START SERVERS PER CAMERA
    // -----------------------------------------------
    function startCameraServers(cam, index) {
      const httpPort = BASE_HTTP_PORT + cam.id;
      const wsPort = BASE_WS_PORT + cam.id;

      initCameraHealth(cam, httpPort, wsPort);

      console.log("\n====================================");
      console.log(`Camera #${index + 1}`);
      console.log(`ID       : ${cam.id}`);
      console.log(`Name     : ${cam.name}`);
      console.log(`RTSP     : ${cam.rtsp_url}`);
      console.log(`FFMPEG → : http://127.0.0.1:${httpPort}/stream`);
      console.log(`WS OUT   : ws://YOUR_NODE_PC_IP:${wsPort}`);
      console.log("====================================\n");

      // WebSocket server for JSMpeg clients
      let wsServer;
      try {
        wsServer = new WebSocket.Server({
          port: wsPort,
          perMessageDeflate: false, // reduce CPU & latency
        });
      } catch (error) {
        console.error(
          `[ERROR] Could not open WS port ${wsPort}. Already in use?\n${error.message}`,
        );
        // process.exit(1);
      }

      wsServer.on("connection", (socket) => {
        setCameraStatus(cam.id, {
          viewers: wsServer.clients.size,
        });
        console.log(
          `[CAM ${cam.id}] Viewer connected (${wsServer.clients.size} total)`,
        );

        socket.on("close", () => {
          setCameraStatus(cam.id, {
            viewers: wsServer.clients.size,
          });
          console.log(
            `[CAM ${cam.id}] Viewer disconnected (${wsServer.clients.size} total)`,
          );
        });
      });

      // HTTP ingest server (FFmpeg pushes MPEG-TS here)
      const httpServer = http.createServer((req, res) => {
        // Incoming MPEG-TS from FFmpeg: forward chunks to all WS clients
        req.on("data", (chunk) => {
          wsServer.clients.forEach((client) => {
            if (client.readyState !== WebSocket.OPEN) return;

            // Backpressure guard: if client buffer too large, skip sending more
            if (client.bufferedAmount > MAX_WS_BUFFERED_AMOUNT) {
              // Optionally: log or close slow client here
              return;
            }

            client.send(chunk);
          });
        });

        req.on("end", () => {
          res.end();
        });

        // Prevent HTTP socket idle timeout for this long-lived stream
        req.socket.setTimeout(0);

        req.on("close", () => {
          console.log(`[CAM ${cam.id}] HTTP ingest client disconnected`);
        });
      });

      // IMPORTANT: disable Node's default 5-minute request timeouts
      httpServer.requestTimeout = 0; // disable per-request 5 min timeout
      httpServer.headersTimeout = 0; // disable header timeout
      httpServer.keepAliveTimeout = 0; // explicit for long single request

      httpServer.listen(httpPort, "0.0.0.0", () => {
        console.log(
          `[CAM ${cam.id}] HTTP ingest listening on port ${httpPort}`,
        );
      });

      // Start FFmpeg for this camera
      startFFmpeg(cam, httpPort);
    }

    // -----------------------------------------------
    // CAMERA HEALTH JSON API
    // -----------------------------------------------
    function startHealthApiServer() {
      const server = http.createServer((req, res) => {
        if (req.url === "/health" || req.url === "/health/") {
          const body = {
            service: "xtremeparking-camera-stream",
            now: nowIso(),
            uptimeSeconds: process.uptime(),
            cameras: Object.values(cameraHealth),
          };

          const json = JSON.stringify(body, null, 2);
          res.statusCode = 200;
          res.setHeader("Content-Type", "application/json; charset=utf-8");
          res.setHeader("Content-Length", Buffer.byteLength(json));
          res.end(json);
          return;
        }

        // Simple 404 for other paths
        res.statusCode = 404;
        res.setHeader("Content-Type", "application/json; charset=utf-8");
        res.end(JSON.stringify({ error: "Not found" }));
      });

      server.listen(HEALTH_PORT, "0.0.0.0", () => {
        console.log(
          `Camera Health API listening on http://127.0.0.1:${HEALTH_PORT}/health`,
        );
      });
    }

    // -----------------------------------------------
    // MAIN EXECUTION
    // -----------------------------------------------
    async function main() {
      try {
        // 1) Load BASE_HTTP_PORT / BASE_WS_PORT from Laravel config API
        await loadPortsFromApi();

        // 2) Start Health API server
        startHealthApiServer();

        // 3) Fetch cameras from Laravel
        const cameras = await fetchCameras();

        // 4) Start servers per camera
        cameras.forEach((cam, index) => startCameraServers(cam, index));

        console.log("\nAll camera live stream servers started.\n");
      } catch (err) {
        console.error("Fatal Error:", err.message);
        //process.exit(1);
      }
    }

    main();
  } catch (e) {
    logger(`ERROR: ${e?.stack || e?.message || String(e)}`);
  }
};
