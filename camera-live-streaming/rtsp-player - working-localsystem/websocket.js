// server.js
// Node server to:
// 1) Receive MPEG-TS stream from FFmpeg over HTTP (port 8081, /stream)
// 2) Broadcast it to browser clients over WebSocket (port 8082)

const http = require("http");
const WebSocket = require("ws");

const STREAM_PORT = 8081; // FFmpeg pushes here
const WS_PORT = 8082; // Browser connects here (WebSocket)

// --- WebSocket server (to browsers) ---
const wsServer = new WebSocket.Server({ port: WS_PORT });

wsServer.on("connection", (socket) => {
  console.log("✅ WebSocket client connected");

  socket.on("close", () => {
    console.log("❌ WebSocket client disconnected");
  });
});

// --- HTTP server (FFmpeg pushes MPEG-TS here) ---
const streamServer = http.createServer((req, res) => {
  console.log("📡 Incoming request:", req.method, req.url);

  if (req.url !== "/stream") {
    res.writeHead(404);
    return res.end("Not found");
  }

  console.log("✅ FFmpeg stream connected:", req.socket.remoteAddress);

  req.on("data", (chunk) => {
    // Broadcast MPEG-TS chunk to all WebSocket clients
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

streamServer.listen(STREAM_PORT, () => {
  console.log(
    `📺 Stream server listening on:  http://127.0.0.1:${STREAM_PORT}/stream`
  );
});

wsServer.on("listening", () => {
  console.log(`🌐 WebSocket server listening on: ws://127.0.0.1:${WS_PORT}`);
});
