/**
 * organize_files_by_date.js
 * - Moves files into YYYYMMDD folders (except today) inside WATCH_DIR/COMPANY_ID
 * - Runs every hour
 * - If WATCH_DIR size > 20GB, deletes oldest YYYYMMDD folders (inside COMPANY_ID) until under limit
 */
const fs = require("fs");
const path = require("path");
const axios = require("axios");
require("dotenv").config(); // fallback
//

let COMPANY_ID = process.env.COMPANY_ID;
let ROOT_DIR = process.env.WATCH_DIR || "./inbox"; // <-- size check here
let baseDir = ROOT_DIR + (COMPANY_ID ? `/${COMPANY_ID}` : "");

const MAX_ROOT_SIZE_BYTES = 10 * 1024 * 1024 * 1024; // 10 GB

console.log("ROOT_DIR:", ROOT_DIR);
console.log("baseDir :", baseDir);

/**
 * Recursively calculate directory size in bytes
 */
function getDirectorySize(dirPath) {
  let total = 0;

  if (!fs.existsSync(dirPath)) return 0;

  const entries = fs.readdirSync(dirPath, { withFileTypes: true });

  for (const entry of entries) {
    const fullPath = path.join(dirPath, entry.name);
    try {
      if (entry.isFile()) {
        const stat = fs.statSync(fullPath);
        total += stat.size;
      } else if (entry.isDirectory()) {
        total += getDirectorySize(fullPath);
      } else {
        continue; // skip symlinks/others
      }
    } catch (e) {
      console.warn("⚠️ Error reading", fullPath, e.message);
    }
  }

  return total;
}

/**
 * Recursively delete a directory
 */
function deleteDirectoryRecursive(dirPath) {
  try {
    if (fs.existsSync(dirPath)) {
      fs.rmSync(dirPath, { recursive: true, force: true });
      console.log("🗑️ Deleted folder:", dirPath);
    }
  } catch (e) {
    console.error("❌ Failed to delete folder", dirPath, e.message);
  }
}

/**
 * If WATCH_DIR size > 20GB, delete oldest YYYYMMDD folders from baseDir
 */
function cleanupOldFoldersIfOverLimit() {
  try {
    if (!fs.existsSync(ROOT_DIR)) return;

    let rootSize = getDirectorySize(ROOT_DIR);
    console.log(
      new Date().toLocaleString(),
      `📦 WATCH_DIR size: ${(rootSize / (1024 * 1024 * 1024)).toFixed(2)} GB`,
    );

    if (rootSize <= MAX_ROOT_SIZE_BYTES) {
      return; // nothing to clean
    }

    console.log(
      "⚠️ WATCH_DIR exceeds 20GB. Starting cleanup of old date folders from baseDir...",
    );

    if (!fs.existsSync(baseDir)) {
      console.warn(
        "⚠️ baseDir does not exist, no date folders to clean:",
        baseDir,
      );
      return;
    }

    // Get list of YYYYMMDD folders in baseDir
    const candidates = fs
      .readdirSync(baseDir)
      .filter((name) => /^\d{8}$/.test(name)) // folder name exactly YYYYMMDD
      .map((name) => ({
        name,
        fullPath: path.join(baseDir, name),
      }))
      .filter((item) => {
        try {
          return fs.lstatSync(item.fullPath).isDirectory();
        } catch {
          return false;
        }
      });

    // Sort by folder name ascending (YYYYMMDD → oldest first)
    candidates.sort((a, b) => a.name.localeCompare(b.name));

    for (const folder of candidates) {
      if (rootSize <= MAX_ROOT_SIZE_BYTES) break;

      const folderSize = getDirectorySize(folder.fullPath);
      console.log(
        `➡️ Deleting old folder ${folder.name}, size: ${(
          folderSize /
          (1024 * 1024 * 1024)
        ).toFixed(3)} GB`,
      );

      deleteDirectoryRecursive(folder.fullPath);
      rootSize -= folderSize;

      console.log(
        `📉 New WATCH_DIR size estimate: ${(
          rootSize /
          (1024 * 1024 * 1024)
        ).toFixed(2)} GB`,
      );
    }

    if (rootSize > MAX_ROOT_SIZE_BYTES) {
      console.warn(
        "⚠️ Cleanup finished but WATCH_DIR still above limit (maybe other non-date folders are large).",
      );
    } else {
      console.log("✅ Cleanup done. WATCH_DIR is within 20GB limit.");
    }
  } catch (err) {
    console.error("❌ Error during cleanup:", err.message);
  }
}

function organizeFiles() {
  // const baseDir = path.join(
  //   "D:",
  //   "projects",
  //   "vehicleparkingbills",
  //   "parking_camera_logs"
  // );

  // console.log("baseDir", baseDir);

  try {
    if (!fs.existsSync(baseDir)) {
      console.warn(
        new Date().toLocaleString(),
        "⚠️ baseDir does not exist, skipping11:",
        baseDir,
      );
      return;
    }

    // const todayStr = new Date().toISOString().slice(0, 10).replace(/-/g, "");
    const todayStr = new Date().toLocaleDateString("en-CA").replace(/-/g, "");

    const files = fs.readdirSync(baseDir);

    for (const file of files) {
      const match = file.match(/^(\d{8})/);
      if (!match) continue; // skip files without YYYYMMDD prefix

      const datePrefix = match[1];
      if (datePrefix === todayStr) continue; // skip today's files

      const src = path.join(baseDir, file);

      // skip if it's a directory (we only move plain files)
      if (fs.lstatSync(src).isDirectory()) continue;

      const destDir = path.join(baseDir, datePrefix);
      if (!fs.existsSync(destDir)) {
        fs.mkdirSync(destDir, { recursive: true });
      }

      const dest = path.join(destDir, file);
      fs.renameSync(src, dest);
    }

    console.log(
      new Date().toLocaleString(),
      "✅ Organized files (skipped today's files).",
    );

    // After organizing, check WATCH_DIR size and clean if needed
    cleanupOldFoldersIfOverLimit();
  } catch (err) {
    console.error(
      new Date().toLocaleString(),
      "❌ Error organizing files:",
      err.message,
    );
  }
}
async function loadConfig() {
  try {
    console.log(
      "🔄 Loading config from API http://127.0.0.1:8000/api/envsettings ...",
    );

    const res = await axios.get("http://127.0.0.1:8000/api/envsettings", {
      timeout: 1000 * 10,
    });

    const cfg = res.data || {};

    // Direct mapping from your JSON
    COMPANY_ID = String(cfg.COMPANY_ID ?? process.env.COMPANY_ID ?? "").trim();

    ROOT_DIR = String(
      cfg.WATCH_DIR ?? process.env.WATCH_DIR ?? "./inbox",
    ).trim();

    // optional: host if you need later
    const HOST = cfg.host || null;

    baseDir = ROOT_DIR + (COMPANY_ID ? `/${COMPANY_ID}` : "");

    console.log("✅ Config loaded from API");
    console.log("COMPANY_ID:", COMPANY_ID || "(none)");
    console.log("ROOT_DIR  :", ROOT_DIR);
    console.log("baseDir   :", baseDir);
    if (HOST) console.log("HOST      :", HOST);
  } catch (e) {
    console.error(
      "❌ Failed to load config from API, using .env instead:",
      e.message,
    );

    COMPANY_ID = String(process.env.COMPANY_ID ?? "").trim();
    ROOT_DIR = String(process.env.WATCH_DIR ?? "./inbox").trim();
    baseDir = ROOT_DIR + (COMPANY_ID ? `/${COMPANY_ID}` : "");

    console.log("COMPANY_ID (env):", COMPANY_ID || "(none)");
    console.log("ROOT_DIR  (env) :", ROOT_DIR);
    console.log("baseDir   (env) :", baseDir);
  }
}

/**
 * Bootstrap: load config, then start organizer + interval
 */
async function startOrganizer() {
  await loadConfig();

  // Run immediately, then repeat every 1 hour
  organizeFiles();
  setInterval(organizeFiles, 60 * 60 * 1000); // every 1 hour
}

module.exports = {
  startOrganizer,
};
