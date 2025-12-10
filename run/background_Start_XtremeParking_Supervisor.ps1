# XtremeParking Supervisor with ONE ROOT directory variable
# Starts + monitors services. Does NOT kill anything.
# CTRL+C calls STOP_XtremeParking_Services.bat to stop services.

$ErrorActionPreference = 'Stop'
[Console]::Title = 'XtremeParking - Supervisor (Background, Auto-Restart)'
[Console]::TreatControlCAsInput = $true

# -------- ROOT PATH --------
$ROOT = 'D:\projects\vehicleparkingbills\xtremeparking'

# -------- ALL DIRECTORIES FROM ROOT --------
$BACKEND        = Join-Path $ROOT 'backend'
$FRONTEND       = Join-Path $ROOT 'frontend'
$NODE           = Join-Path $ROOT 'nodescript'
$CAMERA_STREAM  = Join-Path $ROOT 'camera_live_stream'
$LOG_ROOT       = Join-Path $ROOT 'Log'
$StopBat        = Join-Path $ROOT 'run\STOP_XtremeParking_Services.bat'

# -------- TOOLS --------
$MOSQ_EXE  = 'C:\Program Files\mosquitto\mosquitto.exe'
$MOSQ_CONF = 'C:\Program Files\mosquitto\mosquitto.conf'
$NPX       = Join-Path $env:ProgramFiles 'nodejs\npx.cmd'

$CHECK_SECONDS = 60
$COOLDOWN_SECONDS = 15
$MAX_RESTARTS_WINDOW_SEC = 300
$MAX_RESTARTS_IN_WINDOW  = 10

# -------- DATE + LOG SETUP --------
$TODAY   = (Get-Date).ToString('yyyy-MM-dd')
$LOG_DIR = Join-Path $LOG_ROOT $TODAY
if (!(Test-Path $LOG_DIR)) { New-Item -Path $LOG_DIR -ItemType Directory -Force | Out-Null }

Write-Host "========================================"
Write-Host "  XtremeParking Supervisor - $TODAY"
Write-Host "  ROOT : $ROOT"
Write-Host "  LOGS : $LOG_DIR"
Write-Host "========================================`n"

# -------- GLOBAL ERROR LOG --------
$GLOBAL_ERROR_LOG = Join-Path $LOG_DIR "errors_all_$TODAY.log"

function Write-ErrorAllLog {
  param([string]$Message)
  $ts = Get-Date -Format 'yyyy-MM-dd HH:mm:ss'
  try {
    "[$ts] $Message" | Out-File -FilePath $GLOBAL_ERROR_LOG -Encoding UTF8 -Append -ErrorAction Stop
  } catch {
    Write-Host "Failed to write to error log: $Message ($($_.Exception.Message))" -ForegroundColor Red
  }
}

# -------- BASIC HELPERS --------
function Assert-File {
  param([string]$Path,[string]$Hint)
  if (-not (Test-Path $Path)) {
    throw "Missing: $Path ($Hint)"
  }
}

function Assert-ExeOnPath {
  param([string]$ExeName,[string]$Hint)
  $cmd = Get-Command $ExeName -ErrorAction SilentlyContinue
  if (-not $cmd) {
    throw "Missing executable '$ExeName'. $Hint"
  }
}

function Test-PortListening([int]$Port){
  try {
    return [bool](Get-NetTCPConnection -State Listen -LocalPort $Port -ErrorAction Stop)
  } catch {
    return $false
  }
}

# -------- START PROCESS FUNCTION (FIXED) --------
function Start-ManagedProcess {
  param(
    [string]$Name,
    [string]$Cwd,
    [string]$Cmd,
    [string]$Log
  )

  if (-not (Test-Path (Split-Path $Log -Parent))) {
    New-Item -Path (Split-Path $Log -Parent) -ItemType Directory -Force | Out-Null
  }

  # Try to write header to log, but do NOT crash if file is in use
  try {
    "`n===== START: $Name @ $(Get-Date) =====`n" |
      Out-File -FilePath $Log -Encoding UTF8 -Append -ErrorAction Stop
  } catch {
    Write-ErrorAllLog "Could not write header to log '$Log' for '$Name': $($_.Exception.Message)"
  }

  $full = "cd /d `"$Cwd`" && ($Cmd) >> `"$Log`" 2>>&1"
  Start-Process "cmd.exe" -ArgumentList "/c", $full -WindowStyle Hidden -PassThru
}

# -------- PREFLIGHT (no killing here) --------
Assert-File $MOSQ_EXE  "Install Mosquitto or fix path."
Assert-File $MOSQ_CONF "Provide mosquitto.conf."
Assert-File $NPX       "Node.js npx.cmd not found. Install Node.js (includes npx)."

Assert-ExeOnPath 'php'  'Install PHP and ensure it is in the PATH.'
Assert-ExeOnPath 'node' 'Install Node.js and ensure it is in the PATH.'

if (-not (Test-Path (Join-Path $FRONTEND 'dist'))) {
  Write-Host "[WARN] Frontend dist/ not found. http-server will run but serve 404s." -ForegroundColor Yellow
}

# -------- SERVICES DEFINITIONS --------
$services = @(
  @{ Name='Parking Laravel Server';         Cwd=$BACKEND;       Cmd='php artisan serve --host=0.0.0.0 --port=8000';                   Log=Join-Path $LOG_DIR "laravel_$TODAY.log";        Proc=$null; LastStart=(Get-Date).AddYears(-1); RestartTimes=@() },
  @{ Name='Parking Queue Worker';           Cwd=$BACKEND;       Cmd='php artisan queue:work --tries=3 --sleep=1 --backoff=3';         Log=Join-Path $LOG_DIR "queue_$TODAY.log";          Proc=$null; LastStart=(Get-Date).AddYears(-1); RestartTimes=@() },
  @{ Name='Parking MQTT Listener';          Cwd=$BACKEND;       Cmd='php artisan mqtt:qrbackgroundlistener';                          Log=Join-Path $LOG_DIR "mqtt_$TODAY.log";           Proc=$null; LastStart=(Get-Date).AddYears(-1); RestartTimes=@() },
  @{ Name='Parking Frontend';               Cwd=$FRONTEND;      Cmd="`"$NPX`" --yes http-server dist -p 3000 --no-clipboard --cors"; Log=Join-Path $LOG_DIR "frontend_$TODAY.log";       Proc=$null; LastStart=(Get-Date).AddYears(-1); RestartTimes=@() },
  @{ Name='Mosquitto MQTT Broker';          Cwd='C:\';          Cmd="`"$MOSQ_EXE`" -c `"$MOSQ_CONF`" -v";                             Log=Join-Path $LOG_DIR "mosquitto_$TODAY.log";      Proc=$null; LastStart=(Get-Date).AddYears(-1); RestartTimes=@() },
  @{ Name='Camera Watcher';                 Cwd=$NODE;          Cmd='node watchCameraImages.js';                                     Log=Join-Path $LOG_DIR "watcher_$TODAY.log";        Proc=$null; LastStart=(Get-Date).AddYears(-1); RestartTimes=@() },
  @{ Name='Camera Organizer';               Cwd=$NODE;          Cmd='node organize_files_by_date.js';                                Log=Join-Path $LOG_DIR "organizer_$TODAY.log";      Proc=$null; LastStart=(Get-Date).AddYears(-1); RestartTimes=@() },
  @{ Name='Camera Live Stream';             Cwd=$CAMERA_STREAM; Cmd='node start_camera_live_stream.js';                                        Log=Join-Path $LOG_DIR "stream_$TODAY.log";         Proc=$null; LastStart=(Get-Date).AddYears(-1); RestartTimes=@() }
)

# -------- START ALL SERVICES --------
foreach ($s in $services) {
  $s.Proc = Start-ManagedProcess -Name $s.Name -Cwd $s.Cwd -Cmd $s.Cmd -Log $s.Log
  $s.LastStart = Get-Date
  Write-Host "[STARTED] $($s.Name)" -ForegroundColor Green
}

# -------- CTRL+C EXIT HANDLER --------
Register-EngineEvent PowerShell.Exiting -Action {
  Write-Host "`nSupervisor exiting. No processes killed here." -ForegroundColor Yellow
  Write-ErrorAllLog "Supervisor exit."
} | Out-Null

Write-Host "`nSupervisor running... (CTRL+C to stop using STOP.bat)"
Write-Host "ROOT PATH = $ROOT`n"

$stop = $false

try {
  while (-not $stop) {

    foreach ($s in $services) {
      if (-not $s.Proc -or $s.Proc.HasExited) {
        $now = Get-Date

        if ($s.LastStart -and ((New-TimeSpan $s.LastStart $now).TotalSeconds -lt $COOLDOWN_SECONDS)) { continue }

        $s.Proc = Start-ManagedProcess -Name $s.Name -Cwd $s.Cwd -Cmd $s.Cmd -Log $s.Log
        $s.LastStart = $now

        Write-Host "[RESTARTED] $($s.Name)" -ForegroundColor Yellow
        Write-ErrorAllLog "Restarted $($s.Name)"
      }
    }

    # Display live status
    $status = ($services | ForEach-Object {
      if ($_.Proc -and -not $_.Proc.HasExited) {
        "$($_.Name):UP"
      } else {
        "$($_.Name):DOWN"
      }
    }) -join " | "

    Write-Host ("[{0}] {1}" -f (Get-Date).ToString("HH:mm:ss"), $status)

    # Sleep with CTRL+C detection
    for ($i=0; $i -lt ($CHECK_SECONDS*5); $i++) {
      if ([Console]::KeyAvailable) {
        $key = [Console]::ReadKey($true)
        if ($key.Key -eq 'C' -and ($key.Modifiers -band [ConsoleModifiers]::Control)) {

          Write-Host "`nCTRL+C detected. Running STOP script..." -ForegroundColor Yellow
          if (Test-Path $StopBat) {
            Start-Process "cmd.exe" -ArgumentList "/c `"$StopBat`"" -Verb RunAs
          } else {
            Write-Host "STOP script not found at $StopBat" -ForegroundColor Red
            Write-ErrorAllLog "STOP script not found at $StopBat"
          }

          $stop = $true
          break
        }
      }
      Start-Sleep -Milliseconds 200
    }
  }
}
finally {
  Write-Host "Supervisor closed."
}
