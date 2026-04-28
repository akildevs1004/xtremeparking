# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Big picture

This repo is the source for **XtremeGuard Parking** — a Windows desktop ANPR (license-plate) parking management application. Electron is just the shell; the real product is three independent processes glued together by the Electron main process at startup.

```
Electron (main.js)
 ├── nginx.exe                    → reverse proxy: :8000 → Laravel (FastCGI :9000), :3000 → Nuxt static
 ├── php-cgi.exe (port 9000)      → Laravel backend (www/)
 ├── php artisan schedule:work    → Laravel cron
 ├── php artisan queue:work       → Laravel queue worker
 ├── php artisan mqtt:subscribe   → device heartbeat / config listener
 ├── php artisan mqtt:qrbackgroundlistener  → QR-code payment listener
 ├── mosquitto.exe                → local MQTT broker
 └── three Node helpers:
     ├── camera_event_watch_helper.js   → chokidar-watches D:/camera_logs/* for *_BACKGROUND files,
     │                                    OCRs / posts each to Laravel `/api/camera_log_listner`,
     │                                    publishes MQTT event
     ├── camera_live_stream_helper.js   → fetches camera list from Laravel, spawns one ffmpeg per camera
     │                                    (RTSP → MPEG-TS), serves to browsers via WebSocket (JSMpeg)
     └── camera_organize_files_by_date_helper.js → moves raw camera files into Y/M/D folders
```

The browser window points at `http://<ip>:3000` (the Nuxt static build served by nginx). The Nuxt frontend talks to Laravel at `http://<ip>:8000/api`. **Both servers must be up before the UI is usable** — `main.js` calls `waitForURL("http://<ip>:8000")` before launching schedule/queue/mqtt workers.

`appDir` differs between dev and packaged builds: `process.cwd()` in dev, `process.resourcesPath` in production (see [helpers.js:19-26](helpers.js#L19-L26)). Anything that resolves paths to `nginx.exe`, `mosquitto/`, `ffmpeg/`, `www/`, or `frontend/` must use this pattern, otherwise it will only work in one mode.

The three top-level pieces live as siblings, **each with its own `package.json` / `composer.json`**:

- [main.js](main.js), [helpers.js](helpers.js), `camera_*.js` — Electron + Node helpers (this directory's `package.json`)
- [www/](www/) — Laravel 9 / PHP 8 backend (its own `composer.json` + bundled `php/php.exe`)
- [frontend/](frontend/) — Nuxt 2 / Vue 2 / Vuetify SPA (its own `package.json`, builds to `frontend/dist/`)

## Common commands

All commands assume you're in `d:/projects/xtremeparking/src` (the directory this file lives in).

### Run the full app in dev

```bash
npm run dev          # launches Electron, which starts nginx + PHP + Node helpers
```

The Nuxt frontend is **not** auto-started by Electron — it serves the prebuilt static output from `frontend/` via nginx. To iterate on Vue code, run the Nuxt dev server separately:

```bash
cd frontend && npm run dev   # nuxt dev server on $LOCAL_IP:$LOCAL_PORT (defaults 0.0.0.0:3000)
```

### Build the installer

```bash
npm run build        # electron-builder → dist/XtremeGuardParking-Setup-<version>.exe
```

`package.json` `extraResources` controls what ships outside `app.asar`: `nginx.exe`, `mosquitto/`, `ffmpeg/`, `www/` (sans `node_modules`/`package.json`), `frontend/dist/`, `vs_redist.exe`. `frontend` ships as the **built output** (`frontend/dist`), so you must `cd frontend && npm run build` (or `generate`) before packaging.

There is also an `npm run obf` script that obfuscates `main.js`, `helpers.js`, `socket.js`, and the camera helpers into `dist/` — used as part of the release flow.

### Laravel (backend) — run from `www/`

```bash
cd www
php artisan migrate                                           # apply migrations
php artisan db:seed --class=ParkingSlotSeeder                 # seed a single class
php artisan schedule:work                                     # run scheduled tasks (Kernel.php)
php artisan queue:work                                        # process queued jobs
php artisan mqtt:subscribe                                    # device heartbeat listener
php artisan mqtt:qrbackgroundlistener                         # QR payment listener
php artisan task:attendance_seeder --company_id=8 --employee_id=5656 --day_count=10
vendor/bin/phpunit                                            # run tests (phpunit.xml)
vendor/bin/phpunit tests/Unit/ExampleTest.php                 # single test file
vendor/bin/phpunit --filter=testMethodName                    # single test method
```

The packaged app uses the bundled `www/php/php.exe` / `www/php/php-cgi.exe` — for local CLI work, your system PHP 8 is fine, but match the version used by `www/php/` to avoid composer surprises.

### Frontend (Nuxt 2) — run from `frontend/`

```bash
cd frontend
npm run dev          # nuxt dev server (HMR)
npm run build        # SSR/SPA build
npm run generate     # static-site generation → frontend/dist (what nginx serves in packaged app)
npm run start        # serve the built app
```

There is **no test runner** configured for the frontend.

### Database

Default backend connection is **Postgres** (`DB_CONNECTION=pgsql`, port 5432, db `postgres`). A SQLite file is committed at `www/database/database.sqlite` but the active `.env` points at Postgres. The bundled installer ships a backup at [database_deploy/parking_backup.backup](database_deploy/parking_backup.backup) which `install_restore_pg.bat` restores into a fresh Postgres.

## Conventions and gotchas

- **Routes are split across many files.** [www/routes/api.php](www/routes/api.php) is a manifest of `include('xxx.php')` lines for ~50 feature areas — when adding an endpoint, find the matching feature file (e.g. [parking.php](www/routes/parking.php), [carwashing.php](www/routes/carwashing.php), [unit.php](www/routes/unit.php)) instead of dumping into `api.php` itself.
- **Nginx config is shipped, not generated.** [conf/nginx.conf](conf/nginx.conf) hardcodes `:8000` (Laravel via FastCGI 9000), `:3000` (frontend), `:5174` (dispenser), `:5175` (caller). Changing ports means editing this file _and_ the matching `.env` / Node helper constant.
- **Camera pipeline is config-driven via Laravel.** Both `camera_event_watch_helper.js` and `camera_live_stream_helper.js` GET `http://127.0.0.1:8000/api/envsettings` at startup for `WATCH_DIR`, `MQTT_SERVER`, `BASE_HTTP_PORT`, `BASE_WS_PORT`, etc. Don't hardcode these — change the env settings endpoint / `.env` instead.
- **Logs go to date-bucketed folders.** [helpers.js](helpers.js) `logger(serviceName, msg)` writes to `logs/YYYY-MM-DD/<ServiceName>.log`; `cleanupOldLogs(daysToKeep)` prunes folders older than N days. Don't `console.log` from Node helpers — use `logger()` so output ends up in the right file.
- **Clock-tampering guard.** [helpers.js:487-503](helpers.js#L487-L503) writes `clock.json` and refuses to start if system time has rolled back > 5 min. If you see "System Time Error" during dev, delete `clock.json`.
- **Machine ID is a hardware fingerprint.** `helpers.js` `generateHardwareFingerprint()` hashes CPU/disk/MAC/baseboard into `machine.id`; `getCachedMachineId()` reads it on subsequent runs. Don't commit this file to a different machine.
- **GPU is disabled deliberately** ([main.js:22-26](main.js#L22-L26)) for older Win10 driver compatibility — don't re-enable hardware acceleration without testing on the deployment targets.
- **PHP-CGI auto-restarts.** [helpers.js:196-232](helpers.js#L196-L232) respawns `php-cgi.exe` 2 s after any crash. If you're debugging a PHP fatal, watch `logs/.../PHP-CGI-9000.log`, not the console.
- **Service shutdown is best-effort.** `stopServices(pid)` shells out to `taskkill /PID ... /T /F`. Orphaned `nginx.exe` / `php-cgi.exe` after a hard kill is normal — `stop-services.bat` is the recovery script and ships as an extra resource.
- **Packaged app uses electron-updater** with a generic provider at `https://backend.smart-queue.com/desktop_updates/`. The in-app menu also offers manual ZIP-based updates ([helpers.js:322-368](helpers.js#L322-L368)) which extract over `process.resourcesPath` and relaunch.
- **Auth uses `@nuxtjs/auth-next` with a 1-year token** ([frontend/nuxt.config.js:99-120](frontend/nuxt.config.js#L99-L120)). `autoLogout: false` is intentional for the kiosk use case.
- **There are two MQTT contexts**: a local broker (`mosquitto.exe` at 127.0.0.1:1883) used for device ↔ backend traffic, and a separate QR-payment broker (`MQTT_QR_CODE_PAYMENT`). Don't conflate them.
