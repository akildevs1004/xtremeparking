# Stress test guide

How to simulate a burst of cars arriving at the parking entrance, to verify the
PHP-CGI worker pool + parallel Node queue + Postgres indexes are doing their job
under load.

The simulator drops fake `*_BACKGROUND.jpg` files into `WATCH_DIR`. The Node
watcher (`camera_event_watch_helper.js`) picks them up via chokidar and POSTs
through the real nginx → PHP-CGI pool → Laravel → Postgres pipeline.

## Prerequisites

1. **Electron must be running** — `npm run dev` (in a separate terminal).
   Wait until all 4 PHP-CGI ports are listening:
   ```bash
   netstat -ano | grep -E ":9000|:9001|:9002|:9003" | grep LISTENING
   ```
2. **Watch dir must exist** — `D:\camera_logs\8\8\`. Create with
   `mkdir -p "D:/camera_logs/8/8"` if missing.
3. **A valid camera_code** — use a real `camera_in_name` or `camera_out_name`
   from your `devices` table. Otherwise Laravel returns 404 "Device not
   registered" and the hot path isn't exercised. Find one with:
   ```bash
   cd www && ./php/php.exe artisan tinker --execute="\$d = DB::table('devices')->select('camera_in_name','camera_out_name')->first(); print_r((array)\$d);"
   ```

## Run the burst

From `d:\projects\xtremeparking\src`:

```bash
node test_simulate_car_burst.js <count> <camera_code>
```

Examples:

```bash
node test_simulate_car_burst.js 7                        # default 7 cars
node test_simulate_car_burst.js 20 BC012D8PAJ7F0FD        # 20 cars
node test_simulate_car_burst.js 50 BC012D8PAJ7F0FD        # push harder
node test_simulate_car_burst.js 100 BC012D8PAJ7F0FD       # find the ceiling
```

## Watch results live

In a separate terminal:

```bash
tail -f logs/$(date +%Y-%m-%d)/WATCH-IMAGES.log
```

What you'll see:

| Line | Meaning |
|---|---|
| `📥 New file:` | File picked up by the watcher |
| `✅ API OK: 200` | Request completed successfully |
| `⚠️ POST attempt 1 failed: 500` | Laravel returned an error |
| `↩️ Already processed, skipping:` | Duplicate filename — file already processed in a previous run |
| `⏱️ Duplicate event within 1 minute, skipping push:` | Same vehicle_id+camera+lane combo seen within dedup window |

What good output looks like for a 20-car burst:
- 4 `📥` lines clustered (parallel queue dispatching all 4 workers at once)
- ~1–2 seconds elapse
- More `📥` + `✅` interleaved as workers free up
- Total wall time first-`📥` to last-`✅` ≈ `count × 0.25–0.4` seconds

## Watch CPU spread (visual proof)

Open Task Manager → **Performance** → **CPU** → right-click the chart →
"Change graph to" → "Logical processors". Run a 50- or 100-car burst and watch
the cores. With the parallelism in place, load spreads across multiple cores
instead of pinning one. That's the visible proof.

## Clean up after testing

The simulator inserts rows into `parking_camera_logs` with plates `TEST0001`,
`TEST0002`, etc. Remove them when done:

```bash
cd www
./php/php.exe artisan tinker --execute="echo DB::delete(\"DELETE FROM parking_camera_logs WHERE log_vehicle_number LIKE 'TEST%'\") . ' rows deleted' . PHP_EOL;"
```

Or via SQL in pgAdmin:

```sql
DELETE FROM parking_camera_logs WHERE log_vehicle_number LIKE 'TEST%';
```

## Troubleshooting

| Symptom | Cause | Fix |
|---|---|---|
| `WATCH_DIR does not exist` | Watch dir wasn't created | `mkdir -p "D:/camera_logs/8/8"` |
| `📥 New file` shows but no `API OK` | nginx or PHP-CGI down | `netstat -ano \| grep 9000`; restart Electron |
| `500` with `"Trailing data"` | Carbon timestamp parse error | Don't change the simulator's filename format |
| `404 Device not registered` | camera_code doesn't match any row in `devices` | Use a real `camera_in_name`/`camera_out_name` from the table |
| Files written but only 1 `📥` line | Watcher's processed-files cache filtered them | `rm logs/processed_files_8.json` and re-run |
| `npm run dev` fails with `Cannot read properties of undefined (reading 'isPackaged')` | `ELECTRON_RUN_AS_NODE=1` in shell env | `unset ELECTRON_RUN_AS_NODE` before running, or remove from system env vars |

## Tunable knobs

To increase parallelism, edit two places consistently:

**1. Node side** — [camera_event_watch_helper.js:373](camera_event_watch_helper.js#L373):

```js
const QUEUE_CONCURRENCY = 4;   // 8, 16, etc.
```

**2. PHP side** — [main.js:68](main.js#L68):

```js
serverPIDs = [9000, 9001, 9002, 9003, 9004, 9005].map(...)
```

…and matching ports in [conf/nginx.conf](conf/nginx.conf) `upstream php_backend { ... }`.

Restart Electron after changes. Keep these two values aligned — Node side too
high without enough PHP workers means PHP becomes the bottleneck; Node side too
low and you're not feeding the pool fully.

## Direct HTTP burst (alternative, PHP-pool only)

If you want to stress only the PHP/Laravel side without the Node watcher in the
loop, fire concurrent POSTs from PowerShell:

```powershell
1..20 | ForEach-Object -Parallel {
  $body = @{
    timestamp='20260428114124000'; vehicle_id="TEST$_";
    filename="20260428114124000_TEST${_}_traffic_passing_BC012D8PAJ7F0FD_in_lane1_BACKGROUND.jpg"
    event_category='traffic'; event_type='passing'; camera_code='BC012D8PAJ7F0FD'
    direction='in'; lane='lane1'; company_id=8
  } | ConvertTo-Json
  Invoke-RestMethod -Method Post -Uri 'http://localhost:8000/api/camera_log_listner' -ContentType 'application/json' -Body $body
} -ThrottleLimit 20
```

This skips the Node queue entirely — useful for measuring PHP pool ceiling
in isolation.
