# XtremeParking Supervisor - single console, dated logs, auto-restart, live tail
# Run via Start_XtremeParking.bat

$ErrorActionPreference = 'Stop'
[Console]::Title = 'XtremeParking - Supervisor (Single Window, Auto-Restart)'

# -------- CONFIG --------
$BACKEND       = 'E:\xtremeparking\backend'
$FRONTEND      = 'E:\xtremeparking\frontend'
$NODE          = 'E:\xtremeparking\nodescript'
$MOSQ_EXE      = 'C:\Program Files\mosquitto\mosquitto.exe'
$MOSQ_CONF     = 'C:\Program Files\mosquitto\mosquitto.conf'
$LOG_DIR       = 'E:\xtremeparking\Log'
$CHECK_SECONDS = 60

# -------- DATE + LOGS --------
$TODAY = (Get-Date).ToString('yyyy-MM-dd')
if (-not (Test-Path $LOG_DIR)) { New-Item -ItemType Directory -Path $LOG_DIR | Out-Null }

Write-Host "============================================================"
Write-Host " XtremeParking Supervisor started  $(Get-Date)"
Write-Host " Logs dir: $LOG_DIR (date suffix: $TODAY)"
Write-Host "============================================================`n"

# -------- Service list --------
# Each service: Name, Cwd, Cmd, Log, Proc (runtime)
$services = @(
    @{
        Name='Mosquitto MQTT'
        Cwd = 'C:\'
        Cmd = "`"$MOSQ_EXE`" -c `"$MOSQ_CONF`" -v"
        Log = Join-Path $LOG_DIR "mosquitto_$TODAY.log"
        Proc=$null
    },
    @{
        Name='Laravel Server'
        Cwd = $BACKEND
        Cmd = 'php artisan serve --host=0.0.0.0 --port=8000'
        Log = Join-Path $LOG_DIR "laravel_server_$TODAY.log"
        Proc=$null
    },
    @{
        Name='Queue Worker'
        Cwd = $BACKEND
        Cmd = 'php artisan queue:work'
        Log = Join-Path $LOG_DIR "queue_worker_$TODAY.log"
        Proc=$null
    },
    @{
        Name='MQTT QR Background'
        Cwd = $BACKEND
        Cmd = 'php artisan mqtt:qrbackgroundlistener'
        Log = Join-Path $LOG_DIR "mqtt_qr_$TODAY.log"
        Proc=$null
    },
    @{
        Name='Frontend HTTP'
        Cwd = $FRONTEND
        # Avoid npx.ps1 policy issues by going through cmd.exe; uses local http-server if present
        Cmd = if (Test-Path (Join-Path $FRONTEND 'node_modules\.bin\http-server.cmd')) {
            "`"" + (Join-Path $FRONTEND 'node_modules\.bin\http-server.cmd') + "`" dist -p 3000"
        } else {
            'cmd /c npx http-server dist -p 3000'
        }
        Log = Join-Path $LOG_DIR "frontend_$TODAY.log"
        Proc=$null
    },
    @{
        Name='Camera Watcher'
        Cwd = $NODE
        Cmd = 'node watchCameraImages.js'
        Log = Join-Path $LOG_DIR "watch_camera_$TODAY.log"
        Proc=$null
    },
    @{
        Name='Organizer'
        Cwd = $NODE
        Cmd = 'node organize_files_by_date.js'
        Log = Join-Path $LOG_DIR "organize_files_$TODAY.log"
        Proc=$null
    }
)

function Start-ServiceProc($svc) {
    if (-not (Test-Path $svc.Cwd)) {
        Write-Host "[ERROR] $($svc.Name): CWD not found: $($svc.Cwd)" -ForegroundColor Red
        return
    }
    # Ensure log file exists
    if (-not (Test-Path $svc.Log)) { New-Item -ItemType File -Path $svc.Log -Force | Out-Null }

    Write-Host ("[START] {0}" -f $svc.Name) -ForegroundColor Cyan
    # Start within same window; redirect output to log
    $args = "/c $($svc.Cmd) >> `"$($svc.Log)`" 2>&1"
    $p = Start-Process -FilePath "cmd.exe" -ArgumentList $args -WorkingDirectory $svc.Cwd -NoNewWindow -PassThru
    $svc.Proc = $p
    Write-Host ("[OK] {0} (PID={1}) -> {2}" -f $svc.Name, $p.Id, $svc.Log) -ForegroundColor Green
}

function Is-Alive($proc) {
    if (-not $proc) { return $false }
    try {
        $p = Get-Process -Id $proc.Id -ErrorAction Stop
        return $true
    } catch { return $false }
}

# Initial start
$services | ForEach-Object { Start-ServiceProc $_ }

Write-Host ""
Write-Host ("======= Monitoring every {0}s (Ctrl+C to stop) =======" -f $CHECK_SECONDS) -ForegroundColor Yellow
Write-Host ""

# Live dashboard loop: checks health + prints last lines of each log
while ($true) {
    # Health check + auto-restart
    foreach ($svc in $services) {
        if (-not (Is-Alive $svc.Proc)) {
            Write-Host ("[WARN] {0} not running. Restarting..." -f $svc.Name) -ForegroundColor Yellow
            Start-ServiceProc $svc
        } else {
            Write-Host ("[OK] {0} alive (PID={1})" -f $svc.Name, $svc.Proc.Id) -ForegroundColor DarkGreen
        }
    }

    # Live view (last 30 lines per service)
    Write-Host ""
    Write-Host ("==================== LIVE LOGS ({0}) ====================" -f $TODAY) -ForegroundColor White
    foreach ($svc in $services) {
        Write-Host ""
        Write-Host ("---- {0} ----" -f $svc.Name) -ForegroundColor Cyan
        try {
            if (Test-Path $svc.Log) {
                Get-Content -Path $svc.Log -Tail 30
            } else {
                Write-Host "(no log yet)"
            }
        } catch {
            Write-Host "(log read error: $($_.Exception.Message))"
        }
    }

    # Wait + clear for next refresh
    Start-Sleep -Seconds $CHECK_SECONDS
    Clear-Host
    [Console]::Title = 'XtremeParking - Supervisor (Single Window, Auto-Restart)'
}
