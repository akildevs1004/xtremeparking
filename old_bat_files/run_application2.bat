@echo off
setlocal EnableExtensions EnableDelayedExpansion
title XtremeParking - Auto-Restart Service Manager (Daily Logs)
color 0A

:: ---------- CONFIG ----------
set "BACKEND=E:\xtremeparking\backend"
set "FRONTEND=E:\xtremeparking\frontend"
set "NODE=E:\xtremeparking\nodescript"
set "MOSQUITTO=C:\Program Files\mosquitto\mosquitto.exe"
set "MOSQ_CONF=C:\Program Files\mosquitto\mosquitto.conf"
set "LOG_DIR=E:\xtremeparking\service_logs"

set "CHECK_INTERVAL=30"          :: seconds between checks
set "COOLDOWN_CYCLES=3"          :: loops to wait after a restart
set "MAX_RESTARTS_PER_LOOP=3"    :: safety cap per loop

:: ---------- PREP ----------
if not exist "%LOG_DIR%" mkdir "%LOG_DIR%"

call :SETLOGFILE
call :LOG "===================================================="
call :LOG "[START] XtremeParking Service Manager at %date% %time%"
call :LOG "===================================================="

:: Verify critical paths early
if not exist "%BACKEND%"   call :LOG "ERROR: BACKEND path missing: %BACKEND%"
if not exist "%FRONTEND%"  call :LOG "ERROR: FRONTEND path missing: %FRONTEND%"
if not exist "%NODE%"      call :LOG "ERROR: NODE path missing: %NODE%"
if not exist "%MOSQUITTO%" call :LOG "ERROR: Mosquitto exe not found: %MOSQUITTO%"
if not exist "%MOSQ_CONF%" call :LOG "ERROR: Mosquitto conf not found: %MOSQ_CONF%"

:: Init cooldowns
set CD_LAR=0
set CD_QWK=0
set CD_MQT=0
set CD_FRT=0
set CD_MSQ=0
set CD_CAM=0
set CD_ORG=0

:: ---------- MAIN LOOP ----------
:MAIN_LOOP
call :SETLOGFILE
set RESTARTS_THIS_LOOP=0
call :LOG "---- Checking services ----"

call :ENSURE "Laravel Server"        "php.*artisan\s+serve"                     "cd /d %BACKEND% && php artisan serve --host=0.0.0.0"                  CD_LAR
call :ENSURE "Queue Worker"          "php.*artisan\s+queue:work"                "cd /d %BACKEND% && php artisan queue:work"                             CD_QWK
call :ENSURE "MQTT QRCode Payments"  "php.*artisan\s+mqtt:qrbackgroundlistener" "cd /d %BACKEND% && php artisan mqtt:qrbackgroundlistener"              CD_MQT
call :ENSURE "Frontend"              "http-server\s+dist\s+-p\s+3000"           "cd /d %FRONTEND% && npx http-server dist -p 3000"                      CD_FRT
call :ENSURE_MOSQ "Mosquitto MQTT"   "%MOSQ_CONF%"                              "\"%MOSQUITTO%\" -c \"%MOSQ_CONF%\" -v"                                 CD_MSQ
call :ENSURE "Camera Watcher"        "node.*watchCameraImages\.js"              "cd /d %NODE% && node watchCameraImages.js"                              CD_CAM
call :ENSURE "Organize Camera Images" "node.*organize_files_by_date\.js"        "cd /d %NODE% && node organize_files_by_date.js"                         CD_ORG

call :LOG "Done. Sleeping %CHECK_INTERVAL%s..."
timeout /t %CHECK_INTERVAL% /nobreak >nul
goto :MAIN_LOOP


:: ================== FUNCTIONS ==================

:SETLOGFILE
for /f "tokens=1-3 delims=/.- " %%a in ("%date%") do (
    set "YYYY=%%c" & set "MM=%%b" & set "DD=%%a"
)
if "%YYYY%"=="" for /f "tokens=1-3 delims=/.- " %%a in ("%date%") do (
    set "YYYY=%%a" & set "MM=%%b" & set "DD=%%c"
)
set "LOG_FILE=%LOG_DIR%\xtremeparking_service_log_%YYYY%-%MM%-%DD%.txt"
goto :eof


:LOG
set "msg=%*"
set "msg=%msg:"=%"
echo [%date% %time%] %msg%
>> "%LOG_FILE%" echo [%date% %time%] %msg%
goto :eof


:ENSURE
set "FRIENDLY=%~1"
set "REGEX=%~2"
set "STARTCMD=%~3"
set "CDVAR=%~4"
for /f "tokens=2 delims==" %%v in ('set %CDVAR% 2^>nul') do set "CURCD=%%v"
if not defined CURCD set "CURCD=0"

set "PID_FOUND="
for /f "usebackq delims=" %%P in (`powershell -NoProfile -Command ^
  "$p=Get-CimInstance Win32_Process ^| Where-Object { $_.CommandLine -and ($_.CommandLine -match '%REGEX%') } ^| Select-Object -First 1 -ExpandProperty ProcessId; if($p){$p}"`) do (
  set "PID_FOUND=%%P"
)

if not defined PID_FOUND (
  if !CURCD! LEQ 0 (
    if !RESTARTS_THIS_LOOP! LSS %MAX_RESTARTS_PER_LOOP% (
      call :LOG "[%FRIENDLY%] Not running. Starting..."
      start "%FRIENDLY%" cmd /k %STARTCMD%
      set /a RESTARTS_THIS_LOOP+=1
      set "%CDVAR%=%COOLDOWN_CYCLES%"
      ping -n 2 127.0.0.1 >nul
    ) else (
      call :LOG "[%FRIENDLY%] WARN: Per-loop restart cap reached; skipping."
    )
  ) else (
    call :LOG "[%FRIENDLY%] Cooldown active (%CURCD% loops remaining)."
  )
) else (
  if !CURCD! GTR 0 set /a CURCD-=1 & set "%CDVAR%=!CURCD!"
  call :LOG "[%FRIENDLY%] OK (PID !PID_FOUND!)."
)
goto :eof


:ENSURE_MOSQ
set "FRIENDLY=%~1"
set "CONF_PATH=%~2"
set "STARTCMD=%~3"
set "CDVAR=%~4"
for /f "tokens=2 delims==" %%v in ('set %CDVAR% 2^>nul') do set "CURCD=%%v"
if not defined CURCD set "CURCD=0"

set "PID_FOUND="
for /f "usebackq delims=" %%P in (`powershell -NoProfile -Command ^
  "$procs=Get-CimInstance Win32_Process ^| Where-Object { $_.Name -ieq 'mosquitto.exe' -and $_.CommandLine };"
  "$hit=$null;"
  "if ($procs) {"
  "  if ('%CONF_PATH%' -and (Test-Path '%CONF_PATH%')) {"
  "    $esc=[Regex]::Escape('%CONF_PATH%');"
  "    $hit=$procs ^| Where-Object { $_.CommandLine -match $esc } ^| Select-Object -First 1;"
  "  }"
  "  if (-not $hit) { $hit=$procs ^| Select-Object -First 1 }"
  "}"
  "if ($hit) { $hit.ProcessId }"
`) do (
  set "PID_FOUND=%%P"
)

if not defined PID_FOUND (
  if !CURCD! LEQ 0 (
    if !RESTARTS_THIS_LOOP! LSS %MAX_RESTARTS_PER_LOOP% (
      call :LOG "[%FRIENDLY%] Not running. Starting..."
      start "%FRIENDLY%" cmd /k %STARTCMD%
      set /a RESTARTS_THIS_LOOP+=1
      set "%CDVAR%=%COOLDOWN_CYCLES%"
      ping -n 2 127.0.0.1 >nul
    ) else (
      call :LOG "[%FRIENDLY%] WARN: Per-loop restart cap reached; skipping."
    )
  ) else (
    call :LOG "[%FRIENDLY%] Cooldown active (%CURCD% loops remaining)."
  )
) else (
  if !CURCD! GTR 0 set /a CURCD-=1 & set "%CDVAR%=!CURCD!"
  call :LOG "[%FRIENDLY%] OK (PID !PID_FOUND!)."
)
goto :eof
