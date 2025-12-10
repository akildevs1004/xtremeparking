# XtremeParking Supervisor with ONE ROOT directory variable
# Starts + monitors services. Does NOT kill anything.
# CTRL+C calls STOP_XtremeParking_Services.bat to stop services.

$ErrorActionPreference = 'Stop'
[Console]::Title = 'XtremeParking - Supervisor (Background, Auto-Restart)'
[Console]::TreatControlCAsInput = $true

# -------- ROOT PATH --------
$ROOT = 'D:\xtremeparking' 

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

# intervals (seconds)
$SERVICE_CHECK_INTERVAL = 60
$LOG_CHECK_INTERVAL     = 5
$COOLDOWN_SECONDS       = 15

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
# For services with Ports, ALL listed ports must be listening → UP; otherwise → DOWN & restart.
$services = @(
  @{ Name='Parking Laravel Server';   Cwd=$BACKEND;       Cmd='php artisan serve --host=0.0.0.0 --port=8000';                   Log=Join-Path $LOG_DIR "laravel_$TODAY.log";   Proc=$null; LastStart=(Get-Date).AddYears(-1); RestartTimes=@(); Ports=@(8000) },
  @{ Name='Parking Queue Worker';     Cwd=$BACKEND;       Cmd='php artisan queue:work --tries=3 --sleep=1 --backoff=3';         Log=Join-Path $LOG_DIR "queue_$TODAY.log";     Proc=$null; LastStart=(Get-Date).AddYears(-1); RestartTimes=@(); Ports=@()     },
  @{ Name='Parking MQTT Listener';    Cwd=$BACKEND;       Cmd='php artisan mqtt:qrbackgroundlistener';                          Log=Join-Path $LOG_DIR "mqtt_$TODAY.log";      Proc=$null; LastStart=(Get-Date).AddYears(-1); RestartTimes=@(); Ports=@()     },
  @{ Name='Parking Frontend';         Cwd=$FRONTEND;      Cmd="`"$NPX`" --yes http-server dist -p 3000 --no-clipboard --cors"; Log=Join-Path $LOG_DIR "frontend_$TODAY.log";  Proc=$null; LastStart=(Get-Date).AddYears(-1); RestartTimes=@(); Ports=@(3000) },
  @{ Name='Mosquitto MQTT Broker';    Cwd='C:\';          Cmd="`"$MOSQ_EXE`" -c `"$MOSQ_CONF`" -v";                             Log=Join-Path $LOG_DIR "mosquitto_$TODAY.log"; Proc=$null; LastStart=(Get-Date).AddYears(-1); RestartTimes=@(); Ports=@(1883) },
  @{ Name='Camera Watcher';           Cwd=$NODE;          Cmd='node watchCameraImages.js';                                     Log=Join-Path $LOG_DIR "watcher_$TODAY.log";   Proc=$null; LastStart=(Get-Date).AddYears(-1); RestartTimes=@(); Ports=@()     },
  @{ Name='Camera Organizer';         Cwd=$NODE;          Cmd='node organize_files_by_date.js';                                Log=Join-Path $LOG_DIR "organizer_$TODAY.log"; Proc=$null; LastStart=(Get-Date).AddYears(-1); RestartTimes=@(); Ports=@()     },
  # Camera Live Stream – edit ports to match your actual HTTP/WS mapping
  @{ Name='Camera Live Stream';       Cwd=$CAMERA_STREAM; Cmd='node start_camera_live_stream.js';                              Log=Join-Path $LOG_DIR "stream_$TODAY.log";    Proc=$null; LastStart=(Get-Date).AddYears(-1); RestartTimes=@(); Ports=@(7081,9991,9992,9993,9994) }
)

# -------- LOG FILES TO DISPLAY INCREMENTALLY --------
$logFiles = @(
  @{ Name='Watcher';   Path = Join-Path $LOG_DIR "watcher_$TODAY.log" },
  @{ Name='ErrorsAll'; Path = $GLOBAL_ERROR_LOG }
)

$logState = @{}
foreach ($lf in $logFiles) {
  $logState[$lf.Path] = @{
    Length = 0
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

  # fallback for other types (not used here, but safe)
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

# -------- FUNCTIONS: SERVICE CHECK + LOG CHECK --------

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

  foreach ($s in $Services.Value) {
    $now = Get-Date

    $portsStatus = Are-ServicePortsAllListening $s

    if ($portsStatus -eq $true) {
      # All ports listening – service OK
      continue
    }
 
    if ($portsStatus -eq $false) {
      # Ports defined and at least one is NOT listening → restart
      if ($s.LastStart -and ((New-TimeSpan $s.LastStart $now).TotalSeconds -lt $COOLDOWN_SECONDS)) {
        continue
      }

      $s.Proc = Start-ManagedProcess -Name $s.Name -Cwd $s.Cwd -Cmd $s.Cmd -Log $s.Log
      $s.LastStart = $now

      Write-Host "[RESTARTED - PORTS DOWN] $($s.Name)" -ForegroundColor Yellow
      Write-ErrorAllLog "Restarted $($s.Name) because one or more ports were not listening."
      continue
    }

    # portsStatus -eq $null → no ports configured: use process handle
    if (-not $s.Proc -or $s.Proc.HasExited) {
      if ($s.LastStart -and ((New-TimeSpan $s.LastStart $now).TotalSeconds -lt $COOLDOWN_SECONDS)) {
        continue
      }

      $s.Proc = Start-ManagedProcess -Name $s.Name -Cwd $s.Cwd -Cmd $s.Cmd -Log $s.Log
      $s.LastStart = $now

      Write-Host "[RESTARTED - PROC DOWN] $($s.Name)" -ForegroundColor Yellow
      Write-ErrorAllLog "Restarted $($s.Name) because process was not running."
    }
  }

  # Display live status after checking services
  $status = ($Services.Value | ForEach-Object {
    if (Is-ServiceUp $_) {
      "$($_.Name):UP"
    } else {
      "$($_.Name):DOWN"
    }
  }) -join " | "

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

$lastServiceCheck = (Get-Date).AddSeconds(-$SERVICE_CHECK_INTERVAL)
$lastLogCheck     = (Get-Date).AddSeconds(-$LOG_CHECK_INTERVAL)

try {
  while (-not $stop) {
    $now = Get-Date

    if ((New-TimeSpan $lastServiceCheck $now).TotalSeconds -ge $SERVICE_CHECK_INTERVAL) {
      Check-Services -Services ([ref]$services)
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
