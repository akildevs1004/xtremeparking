// server.js - stream relay behind Nginx (no TLS directly here)

const http = require("http");
const WebSocket = require("ws");

const STREAM_PORT = 8081; // Nginx --> here for /stream
const WS_PORT = 8082; // Nginx --> here for /ws-stream

// --- WebSocket server for browsers (via Nginx /ws-stream) ---
const wsServer = new WebSocket.Server({ port: WS_PORT });

wsServer.on("listening", () => {
  console.log(`WS listening on ws://0.0.0.0:${WS_PORT}`);
});

wsServer.on("connection", (socket) => {
  console.log("✅ WebSocket client connected");
  socket.on("close", () => console.log("❌ WebSocket client disconnected"));
});

// --- HTTP server for FFmpeg push (via Nginx /stream) ---
const streamServer = http.createServer((req, res) => {
  if (req.url !== "/stream") {
    res.writeHead(404);
    return res.end("Not found");
  }

  console.log("✅ FFmpeg stream connected:", req.socket.remoteAddress);

  req.on("data", (chunk) => {
    wsServer.clients.forEach((client) => {
      if (client.readyState === WebSocket.OPEN) {
        client.send(chunk);
      }
    });
  });

  req.on("end", () => {
    console.log("⚠️ FFmpeg stream ended");
    res.end();
  });

  req.on("error", (err) => {
    console.error("❌ Stream error:", err.message);
  });
});

streamServer.listen(STREAM_PORT, "0.0.0.0", () => {
  console.log(`HTTP listening on http://0.0.0.0:${STREAM_PORT}/stream`);
});
