# background_Start_XtremeParking_Supervisor.ps1
# XtremeParking - Restart Supervisor (BAT friendly)
# FEATURES:
# 1) Stop existing services, start fresh
# 2) Live console: Node Watcher lines + ERROR/CRITICAL from backend/node/camera (timestamped)
#    (Frontend logs are NOT displayed)
# 3) Auto-restart services on crash (HTTP health check for frontend + warmup)
# 4) CRITICAL errors are marked differently (red banner)

$ErrorActionPreference = 'Continue'
[Console]::Title = 'XtremeParking Supervisor (Restart+AutoHeal) - Close/CTRL+C stops all'

# ---------------- ROOT ----------------
$ROOT = 'D:\projects\vehicleparkingbills\xtremeparking'
$BACKEND       = Join-Path $ROOT 'backend'
$FRONTEND      = Join-Path $ROOT 'frontend'
$NODE          = Join-Path $ROOT 'nodescript'
$CAMERA_STREAM = Join-Path $ROOT 'nodescript'
$LOG_DIR       = Join-Path $ROOT 'Log\_supervisor_runtime'

$NPX = Join-Path $env:ProgramFiles 'nodejs\npx.cmd'

if (-not (Test-Path $LOG_DIR)) { New-Item -Path $LOG_DIR -ItemType Directory -Force | Out-Null }

# ---------------- TIME HELPER ----------------
function NowTS { return (Get-Date).ToString("HH:mm:ss") }

# ---------------- CAMERA PORTS ----------------
$CAMERA_COUNT = 2
function Get-CameraPorts([int]$count) {
    $ports = @()
    for ($i=1; $i -le $count; $i++) {
        $ports += (7080 + $i)  # 7081, 7082...
        $ports += (9990 + $i)  # 9991, 9992...
    }
    return $ports
}
$CAMERA_PORTS = Get-CameraPorts $CAMERA_COUNT

# ---------------- HEALTH CHECK INTERVALS ----------------
$SERVICE_CHECK_MS = 2500   # restart if something dies
$LOG_POLL_MS      = 700    # live console log polling

# ---------------- HELPERS ----------------
function Assert-Path { param([string]$Path,[string]$Msg) if (-not (Test-Path $Path)) { throw ("{0}: {1}" -f $Msg, $Path) } }
function Assert-Cmd  { param([string]$Cmd) if (-not (Get-Command $Cmd -ErrorAction SilentlyContinue)) { throw ("Command not found in PATH: {0}" -f $Cmd) } }

function Test-PortListening {
    param([int]$Port)
    try { return [bool](Get-NetTCPConnection -State Listen -LocalPort $Port -ErrorAction Stop) } catch { return $false }
}

function Kill-ListeningPort {
    param([int]$Port)
    try {
        Get-NetTCPConnection -State Listen -LocalPort $Port -ErrorAction SilentlyContinue |
        Select-Object -ExpandProperty OwningProcess -Unique |
        ForEach-Object { try { Stop-Process -Id $_ -Force -ErrorAction SilentlyContinue } catch {} }
    } catch {}
}

function Kill-NodeByScript {
    param([string]$Script)
    try {
        Get-CimInstance Win32_Process -Filter "Name='node.exe'" |
            Where-Object { $_.CommandLine -and $_.CommandLine -like "*$Script*" } |
            ForEach-Object { try { Stop-Process -Id $_.ProcessId -Force -ErrorAction SilentlyContinue } catch {} }
    } catch {}
}

function Kill-ByCmdContains {
    param([string]$ContainsText, [string]$ProcessName = $null)
    try {
        $filter = if ($ProcessName) { "Name='$ProcessName'" } else { "Name='cmd.exe' OR Name='node.exe' OR Name='php.exe'" }
        Get-CimInstance Win32_Process -Filter $filter |
            Where-Object { $_.CommandLine -and $_.CommandLine -like "*$ContainsText*" } |
            ForEach-Object { try { Stop-Process -Id $_.ProcessId -Force -ErrorAction SilentlyContinue } catch {} }
    } catch {}
}

function Hard-Stop-All {
    Write-Host ("[{0}] [STOP] Killing existing listeners/services..." -f (NowTS)) -ForegroundColor Yellow

    foreach ($p in @(8000,3001) + $CAMERA_PORTS) { Kill-ListeningPort $p }

    Kill-NodeByScript 'watchCameraImages.js'
    Kill-NodeByScript 'organize_files_by_date.js'
    Kill-NodeByScript 'start_camera_live_stream.js'

    Get-Process ffmpeg -ErrorAction SilentlyContinue | Stop-Process -Force -ErrorAction SilentlyContinue
}

# Track started wrapper processes (cmd.exe) so we can stop them on exit
$global:StartedProcs = @()

# ---------------- SERVICE DEFINITIONS ----------------
# Notes:
# - Frontend uses HTTP health check + warmup to prevent false restarts.
$services = @(
    @{
        Name="Laravel Backend (8000)"; Dir=$BACKEND;
        Command="php artisan serve --host=0.0.0.0 --port=8000";
        Log="laravel_8000.log"; Ports=@(8000); MatchCmd="artisan serve";
        WarmupSec=8; RestartCooldownSec=5;
        LastRestart=(Get-Date).AddYears(-10); LastStart=(Get-Date).AddYears(-10)
    },
    @{
        Name="Queue Worker"; Dir=$BACKEND;
        Command="php artisan queue:work --tries=3 --sleep=1 --backoff=3";
        Log="queue_worker.log"; Ports=@(); MatchCmd="artisan queue:work";
        WarmupSec=6; RestartCooldownSec=5;
        LastRestart=(Get-Date).AddYears(-10); LastStart=(Get-Date).AddYears(-10)
    },
    @{
        Name="MQTT Listener"; Dir=$BACKEND;
        Command="php artisan mqtt:qrbackgroundlistener";
        Log="mqtt_listener.log"; Ports=@(); MatchCmd="artisan mqtt:qrbackgroundlistener";
        WarmupSec=6; RestartCooldownSec=5;
        LastRestart=(Get-Date).AddYears(-10); LastStart=(Get-Date).AddYears(-10)
    },
    @{
        Name="Frontend (3001)"; Dir=$FRONTEND;
        Command="`"$NPX`" --yes http-server dist -p 3001 --no-clipboard --cors";
        Log="frontend_3001.log"; Ports=@(3001); MatchCmd="http-server dist -p 3001";
        HealthUrl="http://127.0.0.1:3001/"; WarmupSec=12; RestartCooldownSec=12;
        LastRestart=(Get-Date).AddYears(-10); LastStart=(Get-Date).AddYears(-10)
    },
    @{
        Name="Node Watcher"; Dir=$NODE;
        Command="node watchCameraImages.js";
        Log="node_watcher.log"; Ports=@(); MatchCmd="watchCameraImages.js";
        WarmupSec=5; RestartCooldownSec=5;
        LastRestart=(Get-Date).AddYears(-10); LastStart=(Get-Date).AddYears(-10)
    },
    @{
        Name="Node Organizer"; Dir=$NODE;
        Command="node organize_files_by_date.js";
        Log="node_organizer.log"; Ports=@(); MatchCmd="organize_files_by_date.js";
        WarmupSec=5; RestartCooldownSec=5;
        LastRestart=(Get-Date).AddYears(-10); LastStart=(Get-Date).AddYears(-10)
    },
    @{
        Name="Camera Live Stream"; Dir=$CAMERA_STREAM;
        Command="node start_camera_live_stream.js";
        Log="camera_live_stream.log"; Ports=$CAMERA_PORTS; MatchCmd="start_camera_live_stream.js";
        HealthUrl=$null; WarmupSec=12; RestartCooldownSec=12;
        LastRestart=(Get-Date).AddYears(-10); LastStart=(Get-Date).AddYears(-10)
    }
)

# ---------------- START SERVICE (cmd.exe + combined log) ----------------
function Start-Svc {
    param([Parameter(Mandatory=$true)][hashtable]$Svc)

    $logPath = Join-Path $LOG_DIR $Svc.Log
    Write-Host ("[{0}] [START] {1}" -f (NowTS), $Svc.Name) -ForegroundColor Cyan
    Write-Host ("[{0}]        LOG {1}" -f (NowTS), $logPath) -ForegroundColor DarkGray

    try {
        $full = "cd /d `"$($Svc.Dir)`" && ($($Svc.Command)) >> `"$logPath`" 2>>&1"
        $p = Start-Process -FilePath "cmd.exe" -ArgumentList @("/d","/c",$full) -WindowStyle Hidden -PassThru
        if ($p) {
            $global:StartedProcs += $p
            Write-Host ("[{0}]        PID {1}" -f (NowTS), $p.Id) -ForegroundColor DarkGray
        }
        $Svc.LastStart = Get-Date
    } catch {
        Write-Host ("[{0}] [FAIL] {1} : {2}" -f (NowTS), $Svc.Name, $_.Exception.Message) -ForegroundColor Red
    }
}

function Is-ServiceUp {
    param([hashtable]$Svc)

    # Warm-up grace period
    $warm = 0
    if ($Svc.ContainsKey('WarmupSec') -and $Svc.WarmupSec) { $warm = [int]$Svc.WarmupSec }
    if ($warm -gt 0 -and $Svc.ContainsKey('LastStart')) {
        $age = (New-TimeSpan -Start $Svc.LastStart -End (Get-Date)).TotalSeconds
        if ($age -lt $warm) { return $true }
    }

    # Prefer HTTP health check if provided (Frontend best)
    if ($Svc.ContainsKey('HealthUrl') -and $Svc.HealthUrl) {
        try {
            $r = Invoke-WebRequest -Uri $Svc.HealthUrl -UseBasicParsing -TimeoutSec 2
            if ($r.StatusCode -ge 200 -and $r.StatusCode -lt 500) { return $true }
        } catch {
            # fall through
        }
    }

    # Port-based truth
    if ($Svc.Ports -and $Svc.Ports.Count -gt 0) {
        foreach ($p in $Svc.Ports) {
            if (-not (Test-PortListening -Port $p)) { return $false }
        }
        return $true
    }

    # Process command line contains marker
    if ($Svc.MatchCmd -and $Svc.MatchCmd.Trim().Length -gt 0) {
        try {
            $procs = Get-CimInstance Win32_Process -Filter "Name='cmd.exe' OR Name='node.exe' OR Name='php.exe'" |
                     Where-Object { $_.CommandLine -and $_.CommandLine -like "*$($Svc.MatchCmd)*" }
            return ($procs.Count -gt 0)
        } catch { return $false }
    }

    return $false
}

function Restart-ServiceSafe {
    param([hashtable]$Svc, [string]$Reason)

    $now = Get-Date
    $since = ($now - $Svc.LastRestart).TotalSeconds
    if ($since -lt [int]$Svc.RestartCooldownSec) { return }

    Write-Host ""
    Write-Host ("[{0}] [RESTART] {1} (Reason: {2})" -f (NowTS), $Svc.Name, $Reason) -ForegroundColor Yellow

    # Stop by ports
    if ($Svc.Ports -and $Svc.Ports.Count -gt 0) {
        foreach ($p in $Svc.Ports) { Kill-ListeningPort $p }
    }

    # Stop by marker
    if ($Svc.MatchCmd) { Kill-ByCmdContains -ContainsText $Svc.MatchCmd }

    # Stream special: also kill ffmpeg
    if ($Svc.Name -like "*Camera Live Stream*") {
        Get-Process ffmpeg -ErrorAction SilentlyContinue | Stop-Process -Force -ErrorAction SilentlyContinue
    }

    Start-Sleep -Milliseconds 600
    Start-Svc -Svc $Svc
    $Svc.LastRestart = Get-Date
}

# ---------------- LIVE LOG MONITOR ----------------
# NOTE: Frontend logs intentionally excluded from console output
$WATCH_LOGS = @(
    @{ Name = 'Node Watcher';   File = 'node_watcher.log' },
    @{ Name = 'Node Organizer'; File = 'node_organizer.log' },
    @{ Name = 'Camera Stream';  File = 'camera_live_stream.log' },
    @{ Name = 'Laravel';        File = 'laravel_8000.log' },
    @{ Name = 'Queue';          File = 'queue_worker.log' },
    @{ Name = 'MQTT';           File = 'mqtt_listener.log' }
)

$ERROR_PATTERNS = @(
    '(?i)\berror\b',
    '(?i)\bexception\b',
    '(?i)\bfailed\b',
    '(?i)\bcannot\b',
    '(?i)\bundefined\b',
    '(?i)\bsqlstate\b'
)

$CRITICAL_PATTERNS = @(
    '(?i)\bfatal\b',
    '(?i)\bpanic\b',
    '(?i)\bsegmentation fault\b',
    '(?i)\bout of memory\b',
    '(?i)\bkilled\b',
    '(?i)\bEADDRINUSE\b',
    '(?i)\bECONNREFUSED\b',
    '(?i)\bUnhandledPromiseRejection\b',
    '(?i)\bPHP Fatal error\b',
    '(?i)\bMaximum call stack\b',
    '(?i)\bFFmpeg.*(exit|exited|error)\b'
)

$global:LogOffsets = @{}

function Initialize-LogOffsets {
    foreach ($l in $WATCH_LOGS) {
        $path = Join-Path $LOG_DIR $l.File
        if (Test-Path $path) { $global:LogOffsets[$path] = (Get-Item $path).Length } else { $global:LogOffsets[$path] = 0 }
    }
}

function Is-CriticalLine([string]$line) {
    foreach ($p in $CRITICAL_PATTERNS) { if ($line -match $p) { return $true } }
    return $false
}
function Is-ErrorLine([string]$line) {
    foreach ($p in $ERROR_PATTERNS) { if ($line -match $p) { return $true } }
    return $false
}

function Read-NewLogContent {
    foreach ($l in $WATCH_LOGS) {
        $path = Join-Path $LOG_DIR $l.File
        if (-not (Test-Path $path)) { continue }

        if (-not $global:LogOffsets.ContainsKey($path)) { $global:LogOffsets[$path] = 0 }

        $last = [int64]$global:LogOffsets[$path]
        $current = [int64](Get-Item $path).Length
        if ($current -le $last) { continue }

        try {
            $fs = [System.IO.File]::Open($path,[System.IO.FileMode]::Open,[System.IO.FileAccess]::Read,[System.IO.FileShare]::ReadWrite)
            $fs.Seek($last, [System.IO.SeekOrigin]::Begin) | Out-Null
            $buf = New-Object byte[] ($current - $last)
            $read = $fs.Read($buf, 0, $buf.Length)
            $fs.Close()
            if ($read -le 0) { continue }

            $text = [System.Text.Encoding]::UTF8.GetString($buf, 0, $read)
            $lines = $text -split "`r?`n"

            foreach ($line in $lines) {
                if ($line.Trim().Length -eq 0) { continue }

                $ts = NowTS

                # Always show Node Watcher live
                if ($l.Name -eq 'Node Watcher') {
                    Write-Host ("[{0}] [Watcher] {1}" -f $ts, $line) -ForegroundColor Gray
                }

                # CRITICAL
                if (Is-CriticalLine $line) {
                    Write-Host ""
                    Write-Host ("[{0}] CRITICAL [{1}]" -f $ts, $l.Name) -ForegroundColor White -BackgroundColor DarkRed
                    Write-Host ("[{0}] {1}" -f $ts, $line) -ForegroundColor Yellow -BackgroundColor DarkRed
                    Write-Host ""
                    continue
                }

                # ERROR
                if (Is-ErrorLine $line) {
                    Write-Host ""
                    Write-Host ("[{0}] !!! ERROR from {1} !!!" -f $ts, $l.Name) -ForegroundColor Red
                    Write-Host ("[{0}] {1}" -f $ts, $line) -ForegroundColor Yellow
                    Write-Host ""
                }
            }

            $global:LogOffsets[$path] = $current
        } catch {
            # ignore transient read issues
        }
    }
}

# ---------------- HEADER ----------------
Write-Host "==============================================="
Write-Host " XtremeParking Supervisor (Restart + Auto-Heal)"
Write-Host (" Logs: {0}" -f $LOG_DIR)
Write-Host " Live: Node Watcher + ERROR + CRITICAL (timestamped)"
Write-Host " Auto-restart: ON (HTTP check for frontend + warmup)"
Write-Host " Frontend logs: NOT displayed"
Write-Host " Close window or CTRL+C to stop all services"
Write-Host "===============================================`n"

# ---------------- PREFLIGHT ----------------
try {
    Assert-Path $BACKEND       "Backend folder missing"
    Assert-Path $FRONTEND      "Frontend folder missing"
    Assert-Path $NODE          "nodescript folder missing"
    Assert-Path $CAMERA_STREAM "camera_live_stream folder missing"
    Assert-Path $NPX           "npx.cmd not found"

    Assert-Cmd "php"
    Assert-Cmd "node"
    Assert-Cmd "ffmpeg"
} catch {
    Write-Host ("[{0}] [FATAL] {1}" -f (NowTS), $_.Exception.Message) -ForegroundColor Red
    Write-Host "Press any key to exit..."
    $null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
    exit 1
}

# ---------------- STOP FIRST ----------------
Hard-Stop-All | Out-Null
Start-Sleep -Seconds 2

# ---------------- START ALL ----------------
foreach ($s in $services) { Start-Svc -Svc $s }

Write-Host ("`n[{0}] [RUNNING] All services started." -f (NowTS)) -ForegroundColor Green
Write-Host ("[{0}] Live log monitoring enabled." -f (NowTS)) -ForegroundColor Cyan
Write-Host ("[{0}] Auto-restart enabled (crash -> restart)." -f (NowTS)) -ForegroundColor Cyan
Write-Host ("[{0}] Close this window or press CTRL+C to stop ALL.`n" -f (NowTS)) -ForegroundColor Yellow

Initialize-LogOffsets

# ---------------- MAIN LOOP ----------------
$lastServiceCheck = Get-Date
try {
    while ($true) {
        Read-NewLogContent
        Start-Sleep -Milliseconds $LOG_POLL_MS

        $now = Get-Date
        if (($now - $lastServiceCheck).TotalMilliseconds -ge $SERVICE_CHECK_MS) {
            foreach ($svc in $services) {
                if (-not (Is-ServiceUp -Svc $svc)) {
                    Restart-ServiceSafe -Svc $svc -Reason "Not running / ports not listening"
                }
            }
            $lastServiceCheck = Get-Date
        }
    }
}
finally {
    Write-Host ("`n[{0}] [EXIT] Stopping all started processes..." -f (NowTS)) -ForegroundColor Yellow

    foreach ($p in $global:StartedProcs) {
        try {
            if ($p -and -not $p.HasExited) { Stop-Process -Id $p.Id -Force -ErrorAction SilentlyContinue }
        } catch {}
    }

    Hard-Stop-All | Out-Null
    Write-Host ("[{0}] [STOPPED] Everything terminated. Ports should be free." -f (NowTS)) -ForegroundColor Green
}
