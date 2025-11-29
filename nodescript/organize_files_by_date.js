/**
 * organize_files_by_date.js
 * Moves files into YYYYMMDD folders (except today), every hour.
 */

const fs = require("fs");
const path = require("path");
require("dotenv").config();

//const baseDir = process.env.WATCH_DIR; // <-- change this path

const COMPANY_ID = process.env.COMPANY_ID;
const baseDir =
  (process.env.WATCH_DIR || "./inbox") + (COMPANY_ID ? `/${COMPANY_ID}` : "");
console.log(baseDir);
function organizeFiles() {
  const todayStr = new Date().toISOString().slice(0, 10).replace(/-/g, "");
  const files = fs.readdirSync(baseDir);

  for (const file of files) {
    const match = file.match(/^(\d{8})/);
    if (!match) continue;
    const datePrefix = match[1];
    if (datePrefix === todayStr) continue;

    const src = path.join(baseDir, file);
    if (fs.lstatSync(src).isDirectory()) continue;

    const destDir = path.join(baseDir, datePrefix);
    if (!fs.existsSync(destDir)) fs.mkdirSync(destDir, { recursive: true });

    fs.renameSync(src, path.join(destDir, file));
  }

  console.log(
    new Date().toLocaleString(),
    "✅ Organized files (skipped today)."
  );
}

// run immediately, then repeat every 1 hour (3600000 ms)
organizeFiles();
setInterval(organizeFiles, 12 * 60 * 60 * 1000); //every 12 hours
