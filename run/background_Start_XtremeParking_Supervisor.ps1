# XtremeParking Supervisor with ONE ROOT directory variable
# Starts services (if not already running) + monitors status.
# Does NOT kill or restart anything.
# CTRL+C calls STOP_XtremeParking_Services.bat to stop services.

$ErrorActionPreference = 'Stop'
[Console]::Title = 'XtremeParking - Supervisor (Background, Monitor-Only)'
[Console]::TreatControlCAsInput = $true

# -------- ROOT PATH --------
# $ROOT = 'D:\xtremeparking' 
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

# -------- API CONFIG (MONITOR ONLY) --------
$MQTT_API_URL   = 'http://127.0.0.1:8000/api/get_mqtt_server'
$CAMERA_API_URL = 'http://127.0.0.1:8000/api/parking-cameras?company_id=8&login_user_type=company'

# intervals (seconds)
$SERVICE_CHECK_INTERVAL = 60
$LOG_CHECK_INTERVAL     = 5

# -------- CAMERA CONFIG (ports) --------
# How many cameras does the live stream handle?
# Set this to 1, 2, or 3 according to your system.
$CAMERA_COUNT = 2

function Get-CameraPorts {
    param([int]$count)

    $ports = @()
    for ($i = 1; $i -le $count; $i++) {
        # HTTP port: 7080 + i  => 7081, 7082, 7083...
        $ports += (7080 + $i)

        # WebSocket port: 9990 + i => 9991, 9992, 9993...
        $ports += (9990 + $i)
    }

    return $ports
}

$CAMERA_PORTS = Get-CameraPorts -count $CAMERA_COUNT

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

# -------- START PROCESS FUNCTION (header only on first create) --------
function Start-ManagedProcess {
  param(
    [string]$Name,
    [string]$Cwd,
    [string]$Cmd,
    [string]$Log
  )

  $logDir = Split-Path $Log -Parent
  if (-not (Test-Path $logDir)) {
    New-Item -Path $logDir -ItemType Directory -Force | Out-Null
  }

  # Only write header if file does NOT exist yet (first start of the day)
  if (-not (Test-Path $Log)) {
    try {
      "===== START: $Name @ $(Get-Date) =====`r`n" |
        Out-File -FilePath $Log -Encoding UTF8 -ErrorAction Stop
    } catch {
      # Cosmetic; do not spam errors_all
      Write-Host "[WARN] Could not initialize log file $Log for '$Name': $($_.Exception.Message)" -ForegroundColor Yellow
    }
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
# For services with Ports:
#   - On startup: if ports already listening → do NOT start.
#   - Monitoring: only status check, NO restart.
$services = @(
  @{ Name='Parking Laravel Server';   Cwd=$BACKEND;       Cmd='php artisan serve --host=0.0.0.0 --port=8000';                   Log=Join-Path $LOG_DIR "laravel_$TODAY.log";   Proc=$null; Ports=@(8000) },
  @{ Name='Parking Queue Worker';     Cwd=$BACKEND;       Cmd='php artisan queue:work --tries=3 --sleep=1 --backoff=3';         Log=Join-Path $LOG_DIR "queue_$TODAY.log";     Proc=$null; Ports=@()     },
  @{ Name='Parking MQTT Listener';    Cwd=$BACKEND;       Cmd='php artisan mqtt:qrbackgroundlistener';                          Log=Join-Path $LOG_DIR "mqtt_$TODAY.log";      Proc=$null; Ports=@()     },
  @{ Name='Parking Frontend';         Cwd=$FRONTEND;      Cmd="`"$NPX`" --yes http-server dist -p 3000 --no-clipboard --cors"; Log=Join-Path $LOG_DIR "frontend_$TODAY.log";  Proc=$null; Ports=@(3000) },
  @{ Name='Mosquitto MQTT Broker';    Cwd='C:\';          Cmd="`"$MOSQ_EXE`" -c `"$MOSQ_CONF`" -v";                             Log=Join-Path $LOG_DIR "mosquitto_$TODAY.log"; Proc=$null; Ports=@(1883) },
  @{ Name='Camera Watcher';           Cwd=$NODE;          Cmd='node watchCameraImages.js';                                     Log=Join-Path $LOG_DIR "watcher_$TODAY.log";   Proc=$null; Ports=@()     },
  @{ Name='Camera Organizer';         Cwd=$NODE;          Cmd='node organize_files_by_date.js';                                Log=Join-Path $LOG_DIR "organizer_$TODAY.log"; Proc=$null; Ports=@()     },
  # Camera Live Stream – ports generated from CAMERA_COUNT
  @{ Name='Camera Live Stream';       Cwd=$CAMERA_STREAM; Cmd='node start_camera_live_stream.js';                              Log=Join-Path $LOG_DIR "stream_$TODAY.log";    Proc=$null; Ports=$CAMERA_PORTS }
)

# -------- LOG FILES TO DISPLAY INCREMENTALLY --------
$logFiles = @(
  @{ Name='Watcher';   Path = Join-Path $LOG_DIR "watcher_$TODAY.log" },
  @{ Name='ErrorsAll'; Path = $GLOBAL_ERROR_LOG }
)

$logState = @{}
foreach ($lf in $logFiles) {
  $logState[$lf.Path] = @{
    Length      = 0
    Initialized = $false
  }
}

# -------- PORT HELPERS --------
function Get-ServicePorts {
  param($svc)

  if ($svc -is [hashtable]) {
    if ($svc.ContainsKey('Ports') -and $svc['Ports']) {
      return [int[]]$svc['Ports']
    } else {
      return @()
    }
  }

  if ($svc.PSObject.Properties.Name -contains 'Ports' -and $svc.Ports) {
    return [int[]]$svc.Ports
  }

  return @()
}

function Are-ServicePortsAllListening {
  param($svc)

  $ports = Get-ServicePorts $svc
  if (-not $ports -or $ports.Count -eq 0) {
    return $null  # "no ports configured"
  }

  foreach ($p in $ports) {
    if (-not (Test-PortListening -Port $p)) {
      return $false
    }
  }

  return $true
}

# -------- CONFIG MONITOR (API ONLY, NO SIDE EFFECTS) --------
function Check-Configs {
  Write-Host ""
  Write-Host ("===== CONFIG CHECK @ {0} =====" -f (Get-Date).ToString("HH:mm:ss")) -ForegroundColor DarkCyan

  # --- MQTT SERVER CONFIG ---
  try {
    $mqtt = Invoke-RestMethod -Uri $MQTT_API_URL -Method Get -TimeoutSec 5
    Write-Host "[CONFIG] MQTT server API OK" -ForegroundColor Green

    # Use names that don't clash with $Host automatic variable
    $mqttHost = $null
    $mqttTcp  = $null
    $mqttWs   = $null

    if ($mqtt.PSObject.Properties.Name -contains 'mqtt_server') { $mqttHost = $mqtt.mqtt_server }
    elseif ($mqtt.PSObject.Properties.Name -contains 'server')  { $mqttHost = $mqtt.server }
    elseif ($mqtt.PSObject.Properties.Name -contains 'host')    { $mqttHost = $mqtt.host }

    if ($mqtt.PSObject.Properties.Name -contains 'mqtt_port')    { $mqttTcp = $mqtt.mqtt_port }
    elseif ($mqtt.PSObject.Properties.Name -contains 'tcp_port') { $mqttTcp = $mqtt.tcp_port }
    elseif ($mqtt.PSObject.Properties.Name -contains 'port')     { $mqttTcp = $mqtt.port }

    if ($mqtt.PSObject.Properties.Name -contains 'mqtt_ws_port')      { $mqttWs = $mqtt.mqtt_ws_port }
    elseif ($mqtt.PSObject.Properties.Name -contains 'ws_port')       { $mqttWs = $mqtt.ws_port }
    elseif ($mqtt.PSObject.Properties.Name -contains 'websocket_port'){ $mqttWs = $mqtt.websocket_port }

    if ($mqttHost -or $mqttTcp -or $mqttWs) {
      # Fallback values for display
      $h = if ($mqttHost) { $mqttHost } else { '-' }
      $t = if ($mqttTcp)  { $mqttTcp }  else { '-' }
      $w = if ($mqttWs)   { $mqttWs }   else { '-' }

      Write-Host ("[CONFIG] MQTT Host: {0}  TCP: {1}  WS: {2}" -f $h, $t, $w)
    } else {
      # Fallback: dump JSON (monitoring only)
      Write-Host "[CONFIG] MQTT raw response:"
      $mqtt | ConvertTo-Json -Depth 5 | Write-Host
    }
  } catch {
    Write-Host  "[CONFIG] MQTT server API ERROR: $($_.Exception.Message)" -ForegroundColor Red
    Write-ErrorAllLog "MQTT API error: $($_.Exception.Message)"
  }

  # --- CAMERA CONFIG ---
  try {
    $cameras = Invoke-RestMethod -Uri $CAMERA_API_URL -Method Get -TimeoutSec 10

    if ($null -eq $cameras) {
      Write-Host "[CONFIG] Camera API returned no data." -ForegroundColor Yellow
      return
    }

    # Assume list/array of cameras
    $camList = @()
    if ($cameras -is [System.Collections.IEnumerable] -and -not ($cameras -is [string])) {
      $camList = @($cameras)
    } else {
      $camList = @($cameras)
    }

    $count = $camList.Count
    Write-Host ("[CONFIG] Cameras from API: {0}" -f $count) -ForegroundColor Green

    if ($count -gt 0) {
      # Show compact table: id, name, rtsp_url (if available)
      $table = $camList |
        Select-Object `
          @{Name='ID';   Expression={ $_.id }}, `
          @{Name='Name'; Expression={ $_.name }}, `
          @{Name='RTSP'; Expression={ $_.rtsp_url }} |
        Format-Table -AutoSize | Out-String
      Write-Host $table
    }
  } catch {
    Write-Host  "[CONFIG] Camera API ERROR: $($_.Exception.Message)" -ForegroundColor Red
    Write-ErrorAllLog "Camera API error: $($_.Exception.Message)"
  }
}

# -------- FUNCTIONS: SERVICE STATUS + LOG CHECK --------

function Is-ServiceUp {
  param($svc)

  $portsStatus = Are-ServicePortsAllListening $svc

  if ($portsStatus -eq $true) {
    return $true   # all ports listening
  } elseif ($portsStatus -eq $false) {
    return $false  # one or more ports not listening
  }

  # No ports configured – use process handle
  if ($svc.Proc -and -not $svc.Proc.HasExited) {
    return $true
  }

  return $false
}

function Check-Services {
  param([ref]$Services)

  $statusEntries = @()

  foreach ($s in $Services.Value) {
    if (Is-ServiceUp $s) {
      $statusEntries += "$($s.Name):UP"
    } else {
      $statusEntries += "$($s.Name):DOWN"
    }
  }

  $status = $statusEntries -join " | "
  Write-Host ("[{0}] {1}" -f (Get-Date).ToString("HH:mm:ss"), $status)
}

function Check-LogsIncremental {
  param(
    [array]$LogFiles,
    [hashtable]$State
  )

  foreach ($lf in $LogFiles) {
    $path = $lf.Path
    if (-not (Test-Path $path)) {
      continue
    }

    try {
      $fileInfo = Get-Item $path
    } catch {
      continue
    }

    $currentLength = $fileInfo.Length
    $st = $State[$path]

    if (-not $st.Initialized) {
      # First time: skip old content, just mark position
      $st.Length = $currentLength
      $st.Initialized = $true
      $State[$path] = $st
      continue
    }

    if ($currentLength -le $st.Length) {
      $st.Length = $currentLength
      $State[$path] = $st
      continue
    }

    $bytesToRead = $currentLength - $st.Length

    try {
      $fs = [System.IO.File]::Open($path,
        [System.IO.FileMode]::Open,
        [System.IO.FileAccess]::Read,
        [System.IO.FileShare]::ReadWrite
      )
      $fs.Seek($st.Length, [System.IO.SeekOrigin]::Begin) | Out-Null

      $buffer = New-Object byte[] ($bytesToRead)
      $read   = $fs.Read($buffer, 0, $buffer.Length)
      $fs.Close()

      if ($read -gt 0) {
        $text = [System.Text.Encoding]::UTF8.GetString($buffer, 0, $read)
        if ($text.Trim().Length -gt 0) {
          Write-Host ""
          Write-Host ("----- NEW LOG from {0} @ {1} -----" -f $lf.Name, (Get-Date).ToString("HH:mm:ss")) -ForegroundColor Cyan
          Write-Host $text
        }
      }
    } catch {
      Write-ErrorAllLog "Failed to read log '$path': $($_.Exception.Message)"
    }

    $st.Length = $currentLength
    $State[$path] = $st
  }
}

# -------- START ALL SERVICES (NO RESTART LOGIC) --------
foreach ($s in $services) {
  $portsStatus = Are-ServicePortsAllListening $s

  if ($portsStatus -eq $true) {
    # Ports already listening – assume service is already running, do not start another instance
    Write-Host "[ALREADY RUNNING] $($s.Name) (ports listening)" -ForegroundColor Cyan
    continue
  }

  # For services with no ports, or ports not listening → start once
  $s.Proc = Start-ManagedProcess -Name $s.Name -Cwd $s.Cwd -Cmd $s.Cmd -Log $s.Log
  Write-Host "[STARTED] $($s.Name)" -ForegroundColor Green
}

# -------- CTRL+C EXIT HANDLER --------
Register-EngineEvent PowerShell.Exiting -Action {
  Write-Host "`nSupervisor exiting. No processes killed or restarted." -ForegroundColor Yellow
  Write-ErrorAllLog "Supervisor exit."
} | Out-Null

Write-Host "`nSupervisor running... (CTRL+C to stop using STOP.bat)"
Write-Host "ROOT PATH = $ROOT`n"

$stop = $false

$lastServiceCheck = (Get-Date).AddSeconds(-$SERVICE_CHECK_INTERVAL)
$lastLogCheck     = (Get-Date).AddSeconds(-$LOG_CHECK_INTERVAL)

# Initial config print on startup
Check-Configs

try {
  while (-not $stop) {
    $now = Get-Date

    if ((New-TimeSpan $lastServiceCheck $now).TotalSeconds -ge $SERVICE_CHECK_INTERVAL) {
      Check-Services -Services ([ref]$services)
      Check-Configs   # refresh config info at same interval
      $lastServiceCheck = Get-Date
    }

    if ((New-TimeSpan $lastLogCheck $now).TotalSeconds -ge $LOG_CHECK_INTERVAL) {
      Check-LogsIncremental -LogFiles $logFiles -State $logState
      $lastLogCheck = Get-Date
    }

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
      }
    }

    Start-Sleep -Milliseconds 200
  }
}
finally {
  Write-Host "Supervisor closed."
}
