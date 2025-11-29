# XtremeParking Supervisor - Safe stop first, then start (background, auto-restart, daily logs)
# Run:
#   Right-click > Run with PowerShell
#   or:
#   powershell -ExecutionPolicy Bypass -File E:\xtremeparking\run\background_Start_XtremeParking_Supervisor.ps1

$ErrorActionPreference = 'Stop'
[Console]::Title = 'XtremeParking - Supervisor (Background, Auto-Restart)'

# -------- CONFIG --------
$BACKEND   = 'E:\xtremeparking\backend'
$FRONTEND  = 'E:\xtremeparking\frontend'
$NODE      = 'E:\xtremeparking\nodescript'
$MOSQ_EXE  = 'C:\Program Files\mosquitto\mosquitto.exe'
$MOSQ_CONF = 'C:\Program Files\mosquitto\mosquitto.conf'
$NPX       = Join-Path $env:ProgramFiles 'nodejs\npx.cmd'   # avoids npx.ps1 policy problems

$LOG_ROOT  = 'E:\xtremeparking\Log'
$CHECK_SECONDS = 60
$COOLDOWN_SECONDS = 15
$MAX_RESTARTS_WINDOW_SEC = 300
$MAX_RESTARTS_IN_WINDOW  = 10

# -------- DATE + LOGS --------
$TODAY = (Get-Date).ToString('yyyy-MM-dd')
$LOG_DIR = Join-Path $LOG_ROOT $TODAY
if (-not (Test-Path $LOG_DIR)) { New-Item -ItemType Directory -Force -Path $LOG_DIR | Out-Null }

Write-Host "========================================"
Write-Host "  XtremeParking Supervisor - $TODAY"
Write-Host "  Logs: $LOG_DIR"
Write-Host "========================================`n"

# -------- HELPERS --------
function Assert-File { param([string]$Path,[string]$Hint) if (-not (Test-Path $Path)) { throw "Missing: $Path ($Hint)" } }

function Test-PortListening([int]$Port){
  try { return [bool](Get-NetTCPConnection -State Listen -LocalPort $Port -ErrorAction Stop) } catch { return $false }
}

function Wait-PortFree([int]$Port,[int]$TimeoutSec=8){
  $sw=[Diagnostics.Stopwatch]::StartNew()
  while ($sw.Elapsed.TotalSeconds -lt $TimeoutSec) {
    $listening = $false
    try { $listening = [bool](Get-NetTCPConnection -LocalPort $Port -State Listen -ErrorAction Stop) } catch { $listening=$false }
    if (-not $listening) { return $true }
    Start-Sleep -Milliseconds 300
  }
  return $false
}

function Start-ManagedProcess {
  param([string]$Name,[string]$Cwd,[string]$CmdLine,[string]$LogPath)
  if (-not (Test-Path (Split-Path $LogPath -Parent))) {
    New-Item -ItemType Directory -Path (Split-Path $LogPath -Parent) -Force | Out-Null
  }
  "`n======= START: $Name @ $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') =======`n" |
    Out-File -FilePath $LogPath -Encoding utf8 -Append
  $fullCmd = "cd /d `"$Cwd`" && ($CmdLine) >> `"$LogPath`" 2>>&1"
  Start-Process -FilePath "cmd.exe" -ArgumentList "/c", $fullCmd -WindowStyle Hidden -PassThru
}

# -------- STOP REGION (PRE-START) --------
function Get-ChildPids {
  param([int]$ParentPid)
  $map = @{}
  Get-CimInstance Win32_Process | ForEach-Object {
    if (-not $map.ContainsKey($_.ParentProcessId)) { $map[$_.ParentProcessId] = @() }
    $map[$_.ParentProcessId] += $_.ProcessId
  }
  $stack = @($ParentPid)
  $all = New-Object System.Collections.Generic.HashSet[int]
  while ($stack.Count) {
    $curPid = $stack[0]
    $stack = if ($stack.Count -gt 1) { $stack[1..($stack.Count-1)] } else { @() }
    if ($all.Add($curPid)) {
      if ($map.ContainsKey($curPid)) { $stack += $map[$curPid] }
    }
  }
  return $all
}

# patterns to recognize our processes safely (wildcard text, NOT regex)
$CmdlinePatterns = @(
  'php artisan serve',
  'php artisan queue:work',
  'php artisan mqtt:qrbackgroundlistener',
  'http-server dist',
  'watchCameraImages.js',
  'organize_files_by_date.js',
  'mosquitto.exe -c'
)
$NameMatches = @('php.exe','php','node.exe','node','mosquitto.exe','mosquitto','http-server','cmd.exe')

function Matches-Xtreme([CimInstance]$p){
  if ($null -ne $p.Name -and ($NameMatches -contains $p.Name)) { return $true }
  $cl = $p.CommandLine
  if ([string]::IsNullOrWhiteSpace($cl)) { return $false }
  foreach ($pat in $CmdlinePatterns) {
    if ($cl -like "*$pat*") { return $true }
  }
  return $false
}

function Stop-XtremeParkingServices {
  Write-Host "Stopping existing XtremeParking processes..." -ForegroundColor Yellow

  $allProcs = Get-CimInstance Win32_Process
  $procs = $allProcs | Where-Object { Matches-Xtreme $_ }

  if (-not $procs) {
    Write-Host "No matching processes found." -ForegroundColor Green
  } else {
    # Kill by full PID trees (cmd.exe parent + child node/php)
    $pidsToKill = New-Object System.Collections.Generic.HashSet[int]
    foreach ($p in $procs) {
      (Get-ChildPids -ParentPid $p.ProcessId) | ForEach-Object { $pidsToKill.Add($_) | Out-Null }
    }
   $pidList = @()
foreach ($n in $pidsToKill) { $pidList += [int]$n }
    Write-Host ("Killing {0} process(es): {1}" -f $pidList.Count, ($pidList -join ', '))

    foreach ($kPid in $pidList) { try { Stop-Process -Id $kPid -ErrorAction SilentlyContinue } catch {} }
    Start-Sleep -Seconds 1
    foreach ($kPid in $pidList) { try { if (Get-Process -Id $kPid -ErrorAction SilentlyContinue) { Stop-Process -Id $kPid -Force -ErrorAction SilentlyContinue } } catch {} }
  }

  # Wait until critical ports are released
  foreach ($p in 8000,3000,1883) {
    if (-not (Wait-PortFree -Port $p)) {
      Write-Host "[WARN] Port $p still busy after wait; continuing anyway." -ForegroundColor DarkYellow
    }
  }
  Write-Host "Stop phase completed." -ForegroundColor Green
}

# Run stop-before-start
Stop-XtremeParkingServices

# -------- PREFLIGHT --------
Assert-File $MOSQ_EXE  "Install Mosquitto or fix path."
Assert-File $MOSQ_CONF "Provide mosquitto.conf."
Assert-File $NPX       "Node.js not found. Install Node.js (includes npx.cmd)."

if (-not (Test-Path (Join-Path $FRONTEND 'dist'))) {
  Write-Host "[WARN] Frontend dist/ not found. http-server will run but serve 404s." -ForegroundColor Yellow
}

# -------- SERVICES --------
$services = @(
  @{ Name='Parking Laravel Server';         Cwd=$BACKEND;  Cmd='php artisan serve --host=0.0.0.0 --port=8000'; Log=Join-Path $LOG_DIR "laravel_server_$TODAY.log";       Proc=$null; LastStart=(Get-Date).AddYears(-1); Restarts=0; RestartTimes=@() },
  @{ Name='Parking Queue Worker';           Cwd=$BACKEND;  Cmd='php artisan queue:work --tries=3 --sleep=1 --backoff=3';            Log=Join-Path $LOG_DIR "queue_worker_$TODAY.log";          Proc=$null; LastStart=(Get-Date).AddYears(-1); Restarts=0; RestartTimes=@() },
  @{ Name='Parking MQTT QRCode Payments';   Cwd=$BACKEND;  Cmd='php artisan mqtt:qrbackgroundlistener';        Log=Join-Path $LOG_DIR "mqtt_qr_$TODAY.log";               Proc=$null; LastStart=(Get-Date).AddYears(-1); Restarts=0; RestartTimes=@() },
  @{ Name='Parking Frontend';               Cwd=$FRONTEND; Cmd="`"$NPX`" --yes http-server dist -p 3000 --no-clipboard --cors";      Log=Join-Path $LOG_DIR "frontend_$TODAY.log";              Proc=$null; LastStart=(Get-Date).AddYears(-1); Restarts=0; RestartTimes=@() },
  @{ Name='Parking Mosquitto MQTT';         Cwd='C:\';     Cmd="`"$MOSQ_EXE`" -c `"$MOSQ_CONF`" -v";           Log=Join-Path $LOG_DIR "mosquitto_$TODAY.log";             Proc=$null; LastStart=(Get-Date).AddYears(-1); Restarts=0; RestartTimes=@() },
  @{ Name='Parking Camera Watcher';         Cwd=$NODE;     Cmd='node watchCameraImages.js';                    Log=Join-Path $LOG_DIR "camera_watcher_$TODAY.log";        Proc=$null; LastStart=(Get-Date).AddYears(-1); Restarts=0; RestartTimes=@() },
  @{ Name='Parking Organize Camera Images'; Cwd=$NODE;     Cmd='node organize_files_by_date.js';               Log=Join-Path $LOG_DIR "organizer_$TODAY.log";             Proc=$null; LastStart=(Get-Date).AddYears(-1); Restarts=0; RestartTimes=@() }
)

# -------- START ALL --------
foreach ($s in $services) {
  $s.Proc = Start-ManagedProcess -Name $s.Name -Cwd $s.Cwd -CmdLine $s.Cmd -LogPath $s.Log
  $s.LastStart = Get-Date
  Write-Host ("[STARTED] {0}" -f $s.Name) -ForegroundColor Green
}

# -------- CLEANUP ON EXIT --------
$onExit = {
  Write-Host "`nStopping all services..." -ForegroundColor Yellow
  foreach ($s in $services) {
    try {
      if ($s.Proc -and -not $s.Proc.HasExited) {
        Stop-Process -Id $s.Proc.Id -Force -ErrorAction SilentlyContinue
        Write-Host ("Stopped {0}" -f $s.Name)
      }
    } catch {}
  }
}
Register-EngineEvent PowerShell.Exiting -Action $onExit | Out-Null

# -------- MONITOR LOOP --------
Write-Host "`nSupervisor running... (CTRL+C to exit)`n" -ForegroundColor Cyan

try {
  while ($true) {
    foreach ($s in $services) {
      if (-not $s.Proc -or $s.Proc.HasExited) {
        if ((New-TimeSpan -Start $s.LastStart -End (Get-Date)).TotalSeconds -lt $COOLDOWN_SECONDS) { continue }

        # crash-loop protection
        $now = Get-Date
        $s.RestartTimes = @($s.RestartTimes | Where-Object { (New-TimeSpan -Start $_ -End $now).TotalSeconds -lt $MAX_RESTARTS_WINDOW_SEC })
        if ($s.RestartTimes.Count -ge $MAX_RESTARTS_IN_WINDOW) {
          "[HALT] $($s.Name) hit restart limit ($MAX_RESTARTS_IN_WINDOW in ${MAX_RESTARTS_WINDOW_SEC}s). Check logs: $($s.Log)" |
            Tee-Object -FilePath (Join-Path $LOG_DIR "supervisor_$TODAY.log") -Append | Out-Host
          continue
        }

        try {
          $s.Proc = Start-ManagedProcess -Name $s.Name -Cwd $s.Cwd -CmdLine $s.Cmd -LogPath $s.Log
          $s.LastStart = $now
          $s.Restarts++; $s.RestartTimes += $now
          Write-Host ("[RESTARTED] {0} (Total: {1})" -f $s.Name, $s.Restarts) -ForegroundColor Yellow
        } catch {
          Write-Host ("[ERROR] Failed to restart {0}: {1}" -f $s.Name, $_.Exception.Message) -ForegroundColor Red
        }
      }
    }

    # quick port hints
    if (-not (Test-PortListening 1883)) { Write-Host "[HINT] MQTT 1883 not listening. Log: $(Join-Path $LOG_DIR "mosquitto_$TODAY.log")" -ForegroundColor DarkYellow }
    if (-not (Test-PortListening 3000)) { Write-Host "[HINT] Frontend 3000 not listening. Log: $(Join-Path $LOG_DIR "frontend_$TODAY.log")"  -ForegroundColor DarkYellow }
    if (-not (Test-PortListening 8000)) { Write-Host "[HINT] Laravel 8000 not listening. Log: $(Join-Path $LOG_DIR "laravel_server_$TODAY.log")" -ForegroundColor DarkYellow }

    $status = ($services | ForEach-Object {
      if ($_.Proc -and -not $_.Proc.HasExited) { '{0}:UP' -f $_.Name } else { '{0}:DOWN' -f $_.Name }
    }) -join ' | '
    Write-Host ("[{0}] {1}" -f (Get-Date).ToString("HH:mm:ss"), $status)

    Start-Sleep -Seconds $CHECK_SECONDS
  }
}
finally { & $onExit }
