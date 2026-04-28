// Local load test: drops N fake *_BACKGROUND.jpg files into WATCH_DIR to
// simulate a burst of cars arriving at the camera.
//
// Usage:
//   node test_simulate_car_burst.js              # 7 cars (default)
//   node test_simulate_car_burst.js 20           # 20 cars
//   node test_simulate_car_burst.js 20 CAM01     # 20 cars, override camera_code
//
// Reads WATCH_DIR + COMPANY_ID from www/.env so it always targets the same
// directory the watcher is watching. Run while `npm run dev` is up.

const fs = require("fs");
const path = require("path");

require("dotenv").config({ path: path.join(__dirname, "www", ".env") });

const COMPANY_ID = (process.env.COMPANY_ID || "8").toString().trim();
const WATCH_DIR_RAW = (process.env.WATCH_DIR || "").toString().trim();

if (!WATCH_DIR_RAW) {
  console.error("WATCH_DIR is not set in www/.env");
  process.exit(1);
}

// Match camera_event_watch_helper.js: only append COMPANY_ID if it isn't
// already the trailing segment of WATCH_DIR.
const trimmed = WATCH_DIR_RAW.replace(/[\\/]+$/, "");
const watchDir =
  COMPANY_ID && !trimmed.endsWith(COMPANY_ID)
    ? path.join(trimmed, COMPANY_ID)
    : trimmed;

if (!fs.existsSync(watchDir)) {
  console.error("WATCH_DIR does not exist:", watchDir);
  console.error("Create it or fix WATCH_DIR / COMPANY_ID in www/.env first.");
  process.exit(1);
}

const count = parseInt(process.argv[2], 10) || 7;
const cameraCode = (process.argv[3] || "CAM01").toString();

// Smallest valid JPEG (1x1 pixel). OCR is commented out in the controller, so
// the bytes don't matter — only the filename pattern does.
const TINY_JPG = Buffer.from(
  "ffd8ffe000104a46494600010100000100010000ffdb004300080606070605080707070909080a0c140d0c0b0b0c1912130f141d1a1f1e1d1a1c1c20242e2720222c231c1c2837292c30313434341f27393d38323c2e333432ffc00011080001000103012200021101031101ffc4001f0000010501010101010100000000000000000102030405060708090a0bffc400b5100002010303020403050504040000017d01020300041105122131410613516107227114328191a1082342b1c11552d1f02433627282090a161718191a25262728292a3435363738393a434445464748494a535455565758595a636465666768696a737475767778797a838485868788898a92939495969798999aa2a3a4a5a6a7a8a9aab2b3b4b5b6b7b8b9bac2c3c4c5c6c7c8c9cad2d3d4d5d6d7d8d9dae1e2e3e4e5e6e7e8e9eaf1f2f3f4f5f6f7f8f9faffc4001f0100030101010101010101010000000000000102030405060708090a0bffc400b51100020102040403040705040400010277000102031104052131061241510761711322328108144291a1b1c109233352f0156272d10a162434e125f11718191a262728292a35363738393a434445464748494a535455565758595a636465666768696a737475767778797a82838485868788898a92939495969798999aa2a3a4a5a6a7a8a9aab2b3b4b5b6b7b8b9bac2c3c4c5c6c7c8c9cad2d3d4d5d6d7d8d9dae2e3e4e5e6e7e8e9eaf2f3f4f5f6f7f8f9faffda000c03010002110311003f00fbfca28affd9",
  "hex",
);

// Laravel parses the leading token with Carbon format 'YmdHisv' = 17 chars
// (4y + 2m + 2d + 2H + 2i + 2s + 3v). Build it without ms — we'll inject the
// index as the 3-digit millisecond field so each file gets a unique timestamp.
function buildTimestampNoMs() {
  const d = new Date();
  const pad = (n, l = 2) => String(n).padStart(l, "0");
  return (
    d.getFullYear() +
    pad(d.getMonth() + 1) +
    pad(d.getDate()) +
    pad(d.getHours()) +
    pad(d.getMinutes()) +
    pad(d.getSeconds())
  );
}

const baseTs = buildTimestampNoMs();

console.log("Configuration:");
console.log("  WATCH_DIR  :", watchDir);
console.log("  COMPANY_ID :", COMPANY_ID);
console.log("  count      :", count);
console.log("  camera_code:", cameraCode);
console.log("");

const start = Date.now();

const writes = [];
for (let i = 0; i < count; i++) {
  // Each file gets a unique vehicle_id so the watcher's 1-second dedup window
  // doesn't collapse them into one. Index becomes the 3-digit millisecond
  // field so the leading token stays exactly 17 chars (YmdHisv).
  const plate = `TEST${String(i + 1).padStart(4, "0")}`;
  const ms = String(i).padStart(3, "0");
  const filename = `${baseTs}${ms}_${plate}_traffic_passing_${cameraCode}_in_lane1_BACKGROUND.jpg`;
  const full = path.join(watchDir, filename);
  writes.push(fs.promises.writeFile(full, TINY_JPG).then(() => filename));
}

Promise.all(writes)
  .then((names) => {
    const ms = Date.now() - start;
    console.log(`Wrote ${names.length} files in ${ms}ms.`);
    console.log("\nWhat to check:");
    console.log(
      "  1. logs/<YYYY-MM-DD>/WATCH-IMAGES.log — should show ~4 '📥 New file' lines clustered, then another batch",
    );
    console.log(
      "  2. logs/<YYYY-MM-DD>/PHP-CGI-9000.log through PHP-CGI-9003.log — ALL FOUR files should grow (round-robin)",
    );
    console.log(
      "  3. Task Manager → load should spread across cores, not pin one",
    );
    console.log(
      "  4. Time between first and last 'API OK' in WATCH-IMAGES.log → should be ~25% of single-worker time",
    );
  })
  .catch((err) => {
    console.error("Write failed:", err);
    process.exit(1);
  });
