# Production deploy

## 1. Upload these files

| # | File |
|---|---|
| 1 | `main.js` |
| 2 | `conf/nginx.conf` |
| 3 | `camera_event_watch_helper.js` |
| 4 | `www/app/Http/Controllers/Parking/CameraLogListenerController.php` |
| 5 | `database_deploy/add_parking_lookup_indexes.sql` |

## 2. Deployment steps

1. Stop Electron on prod (close window, or `taskkill /F /IM electron.exe`)
2. Also kill leftovers:
   ```
   taskkill /F /IM nginx.exe
   taskkill /F /IM php-cgi.exe
   taskkill /F /IM mosquitto.exe
   ```
3. Upload the 5 files (overwrite existing)
4. Run the SQL once against prod Postgres:
   ```
   "C:\Program Files\PostgreSQL\16\bin\psql.exe" -U postgres -h 127.0.0.1 -d <prod_db> -f "database_deploy\add_parking_lookup_indexes.sql"
   ```
   (adjust PG version + DB name)
5. Start Electron normally
6. Watch first real cars in `logs/<today>/WATCH-IMAGES.log` — should see `📥 New file` followed by `✅ API OK: 200`

## 3. Rollback

- **Code:** restore from git
- **Indexes:** run the `DROP INDEX IF EXISTS ...` block at the bottom of `add_parking_lookup_indexes.sql`

## 4. Don't upload these

`test_simulate_car_burst.js`, `STRESS_TEST.md`, `DEPLOY.md`, `CLAUDE.md`, `clock.json`, `.claude/`
