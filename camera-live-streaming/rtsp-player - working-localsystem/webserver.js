const express = require("express");
const app = express();
const path = require("path");

// Serve all files from current folder
app.use(express.static(path.join(__dirname)));

const PORT = 3333; // change if needed

app.listen(PORT, "0.0.0.0", () => {
  console.log(`🌐 Web server running on: http://localhost:${PORT}`);

  // LAN / WiFi IP
  const os = require("os");
  const nets = os.networkInterfaces();
  Object.values(nets).forEach((ifaces) => {
    ifaces.forEach((iface) => {
      if (iface.family === "IPv4" && !iface.internal) {
        console.log(`📡 LAN URL: http://${iface.address}:${PORT}`);
      }
    });
  });
});
