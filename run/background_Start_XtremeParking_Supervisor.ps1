# background_Start_XtremeParking_Supervisor.ps1
# XtremeParking - Restart Supervisor (BAT friendly)
# - Stops existing services (ports + known node scripts + ffmpeg)
# - Starts backend / frontend / node services (via cmd.exe, combined logs)
# - Shows LIVE Node Watcher log lines in console
# - Highlights ERROR lines from ANY service log in console

$ErrorActionPreference = 'Continue'
[Console]::Title = 'XtremeParking Supervisor (Restart) - Close/CTRL+C stops all'

# ---------------- ROOT ----------------
$ROOT = 'D:\projects\vehicleparkingbills\xtremeparking'
$BACKEND       = Join-Path $ROOT 'backend'
$FRONTEND      = Join-Path $ROOT 'frontend'
$NODE          = Join-Path $ROOT 'nodescript'
$CAMERA_STREAM = Join-Path $ROOT 'nodescript'
$LOG_DIR       = Join-Path $ROOT 'Log\_supervisor_runtime'

$NPX = Join-Path $env:ProgramFiles 'nodejs\npx.cmd'

if (-not (Test-Path $LOG_DIR)) { New-Item -Path $LOG_DIR -ItemType Directory -Force | Out-Null }

# ---------------- PORTS ----------------
$CAMERA_COUNT = 2
function Get-CameraPorts([int]$count) {
    $ports = @()
    for ($i=1; $i -le $count; $i++) {
        $ports += (7080 + $i)  # 7081, 7082...
        $ports += (9990 + $i)  # 9991, 9992...
    }
    return $ports
}
$CAMERA_PORTS   = Get-CameraPorts $CAMERA_COUNT
$CRITICAL_PORTS = @(8000,3001) + $CAMERA_PORTS

# ---------------- HELPERS ----------------
function Assert-Path {
    param([string]$Path,[string]$Msg)
    if (-not (Test-Path $Path)) { throw ("{0}: {1}" -f $Msg, $Path) }
}
function Assert-Cmd {
    param([string]$Cmd)
    if (-not (Get-Command $Cmd -ErrorAction SilentlyContinue)) {
        throw ("Command not found in PATH: {0}" -f $Cmd)
    }
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

function Hard-Stop-All {
    Write-Host "`n[STOP] Killing existing listeners/services..." -ForegroundColor Yellow

    foreach ($p in $CRITICAL_PORTS) { Kill-ListeningPort $p }

    Kill-NodeByScript 'watchCameraImages.js'
    Kill-NodeByScript 'organize_files_by_date.js'
    Kill-NodeByScript 'start_camera_live_stream.js'

    Get-Process ffmpeg -ErrorAction SilentlyContinue | Stop-Process -Force -ErrorAction SilentlyContinue
}

# Track started wrapper processes (cmd.exe) so we can stop them on exit
$global:StartedProcs = @()

# ---------------- START SERVICE (ALL via cmd.exe, 1 log file, stdout+stderr combined) ----------------
function Start-Svc {
    param(
        [Parameter(Mandatory=$true)][string]$Name,
        [Parameter(Mandatory=$true)][string]$Dir,
        [Parameter(Mandatory=$true)][string]$CommandLine,  # e.g. php artisan serve ...
        [Parameter(Mandatory=$true)][string]$LogFile
    )

    $logPath = Join-Path $LOG_DIR $LogFile
    Write-Host "[START] $Name" -ForegroundColor Cyan
    Write-Host "       LOG $logPath" -ForegroundColor DarkGray

    try {
        # Combine output: >> log 2>>&1
        $full = "cd /d `"$Dir`" && ($CommandLine) >> `"$logPath`" 2>>&1"
        $p = Start-Process -FilePath "cmd.exe" `
                           -ArgumentList @("/d","/c",$full) `
                           -WindowStyle Hidden `
                           -PassThru
        if ($p) {
            $global:StartedProcs += $p
            Write-Host "       PID $($p.Id)" -ForegroundColor DarkGray
        }
    } catch {
        Write-Host "[FAIL] $Name : $($_.Exception.Message)" -ForegroundColor Red
    }
}

# ---------------- LIVE LOG MONITOR CONFIG ----------------
$WATCH_LOGS = @(
    @{ Name = 'Node Watcher';   File = 'node_watcher.log' },
    @{ Name = 'Node Organizer'; File = 'node_organizer.log' },
    @{ Name = 'Camera Stream';  File = 'camera_live_stream.log' },
    @{ Name = 'Laravel';        File = 'laravel_8000.log' },
    @{ Name = 'Queue';          File = 'queue_worker.log' },
    @{ Name = 'MQTT';           File = 'mqtt_listener.log' },
    @{ Name = 'Frontend';       File = 'frontend_3001.log' }
)

$ERROR_PATTERNS = @(
    '(?i)\berror\b',
    '(?i)\bexception\b',
    '(?i)\bfatal\b',
    '(?i)\bfailed\b',
    '(?i)\bcannot\b',
    '(?i)\bdenied\b',
    '(?i)\bundefined\b',
    '(?i)\btraceback\b',
    '(?i)\bsqlstate\b'
)

$global:LogOffsets = @{}

function Initialize-LogOffsets {
    foreach ($l in $WATCH_LOGS) {
        $path = Join-Path $LOG_DIR $l.File
        if (Test-Path $path) {
            $global:LogOffsets[$path] = (Get-Item $path).Length
        } else {
            $global:LogOffsets[$path] = 0
        }
    }
}

function Is-ErrorLine([string]$line) {
    foreach ($p in $ERROR_PATTERNS) {
        if ($line -match $p) { return $true }
    }
    return $false
}

function Read-NewLogContent {
    foreach ($l in $WATCH_LOGS) {
        $path = Join-Path $LOG_DIR $l.File
        if (-not (Test-Path $path)) { continue }

        if (-not $global:LogOffsets.ContainsKey($path)) {
            $global:LogOffsets[$path] = 0
        }

        $last = [int64]$global:LogOffsets[$path]
        $current = [int64](Get-Item $path).Length
        if ($current -le $last) { continue }

        try {
            $fs = [System.IO.File]::Open(
                $path,
                [System.IO.FileMode]::Open,
                [System.IO.FileAccess]::Read,
                [System.IO.FileShare]::ReadWrite
            )
            $fs.Seek($last, [System.IO.SeekOrigin]::Begin) | Out-Null

            $buf = New-Object byte[] ($current - $last)
            $read = $fs.Read($buf, 0, $buf.Length)
            $fs.Close()

            if ($read -le 0) { continue }

            $text = [System.Text.Encoding]::UTF8.GetString($buf, 0, $read)
            $lines = $text -split "`r?`n"

            foreach ($line in $lines) {
                if ($line.Trim().Length -eq 0) { continue }

                # Always show Node Watcher lines live
                if ($l.Name -eq 'Node Watcher') {
                    Write-Host ("[Watcher] {0}" -f $line) -ForegroundColor Gray
                }

                # Show errors from any service
                if (Is-ErrorLine $line) {
                    Write-Host ""
                    Write-Host ("!!! ERROR from {0} !!!" -f $l.Name) -ForegroundColor Red
                    Write-Host $line -ForegroundColor Yellow
                    Write-Host ""
                }
            }

            $global:LogOffsets[$path] = $current
        } catch {
            # Avoid crashing due to temporary file access issues
        }
    }
}

# ---------------- HEADER ----------------
Write-Host "==============================================="
Write-Host " XtremeParking Supervisor (Restart)"
Write-Host " Logs: $LOG_DIR"
Write-Host " Live: Node Watcher log + ERROR lines from all"
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
    Write-Host "[FATAL] $($_.Exception.Message)" -ForegroundColor Red
    Write-Host "Press any key to exit..."
    $null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
    exit 1
}

# ---------------- STOP FIRST ----------------
Hard-Stop-All | Out-Null
Start-Sleep -Seconds 2

# ---------------- START SERVICES ----------------
Start-Svc -Name "Laravel Backend (8000)" -Dir $BACKEND `
    -CommandLine "php artisan serve --host=0.0.0.0 --port=8000" `
    -LogFile "laravel_8000.log"

Start-Svc -Name "Queue Worker" -Dir $BACKEND `
    -CommandLine "php artisan queue:work --tries=3 --sleep=1 --backoff=3" `
    -LogFile "queue_worker.log"

Start-Svc -Name "MQTT Listener" -Dir $BACKEND `
    -CommandLine "php artisan mqtt:qrbackgroundlistener" `
    -LogFile "mqtt_listener.log"

Start-Svc -Name "Frontend (3001)" -Dir $FRONTEND `
    -CommandLine "`"$NPX`" --yes http-server dist -p 3001 --no-clipboard --cors" `
    -LogFile "frontend_3001.log"

Start-Svc -Name "Node Watcher" -Dir $NODE `
    -CommandLine "node watchCameraImages.js" `
    -LogFile "node_watcher.log"

Start-Svc -Name "Node Organizer" -Dir $NODE `
    -CommandLine "node organize_files_by_date.js" `
    -LogFile "node_organizer.log"

Start-Svc -Name "Camera Live Stream" -Dir $CAMERA_STREAM `
    -CommandLine "node start_camera_live_stream.js" `
    -LogFile "camera_live_stream.log"

Write-Host "`n[RUNNING] All services started." -ForegroundColor Green
Write-Host "Live log monitoring enabled (Node Watcher + errors)." -ForegroundColor Cyan
Write-Host "Close this window or press CTRL+C to stop ALL.`n" -ForegroundColor Yellow

# Start monitoring from end of current logs (no old spam)
Initialize-LogOffsets

# ---------------- KEEP ALIVE + LIVE LOGS ----------------
try {
    while ($true) {
        Read-NewLogContent
        Start-Sleep -Milliseconds 800
    }
}
finally {
    Write-Host "`n[EXIT] Stopping all started processes..." -ForegroundColor Yellow

    foreach ($p in $global:StartedProcs) {
        try {
            if ($p -and -not $p.HasExited) {
                Stop-Process -Id $p.Id -Force -ErrorAction SilentlyContinue
            }
        } catch {}
    }

    Hard-Stop-All | Out-Null
    Write-Host "[STOPPED] Everything terminated. Ports should be free." -ForegroundColor Green
}
