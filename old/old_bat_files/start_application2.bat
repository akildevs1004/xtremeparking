@echo off
setlocal EnableExtensions EnableDelayedExpansion
title XtremeParking - All Services (Single Window)
color 0A

echo ==============================================
echo        Starting XtremeParking Services...
echo ==============================================
echo.

:: ---------- CONFIG ----------
set "BACKEND=E:\xtremeparking\backend"
set "FRONTEND=E:\xtremeparking\frontend"
set "NODE=E:\xtremeparking\nodescript"
set "MOSQUITTO_EXE=C:\Program Files\mosquitto\mosquitto.exe"
set "MOSQUITTO_CONF=C:\Program Files\mosquitto\mosquitto.conf"
set "LOG_DIR=E:\xtremeparking\Log"

:: ---------- DATE FORMATTING ----------
for /f "tokens=2-4 delims=/.- " %%a in ('date /t') do (
    set "YYYY=%%c"
    set "MM=%%a"
    set "DD=%%b"
)
if "%YYYY%"=="" (
    rem fallback for regional date format (e.g. Tue 10/14/2025)
    for /f "tokens=2-4 delims=/.- " %%a in ('date /t') do (
        set "MM=%%a"
        set "DD=%%b"
        set "YYYY=%%c"
    )
)
set "TODAY=%YYYY%-%MM%-%DD%"

:: Create log folder
if not exist "%LOG_DIR%" mkdir "%LOG_DIR%"

echo Logs will be saved with date suffix: %TODAY%
echo ==============================================================
echo.

:: ---------- START MOSQUITTO ----------
echo [1/7] Starting Mosquitto MQTT Broker...
start /b "" "%MOSQUITTO_EXE%" -c "%MOSQUITTO_CONF%" -v >> "%LOG_DIR%\mosquitto_%TODAY%.log" 2>&1

:: ---------- START LARAVEL SERVER ----------
echo [2/7] Starting Laravel Server...
cd /d "%BACKEND%"
start /b cmd /c "php artisan serve --host=0.0.0.0 --port=8000 >> "%LOG_DIR%\laravel_server_%TODAY%.log" 2>&1"

:: ---------- START QUEUE WORKER ----------
echo [3/7] Starting Laravel Queue Worker...
start /b cmd /c "php artisan queue:work >> "%LOG_DIR%\queue_worker_%TODAY%.log" 2>&1"

:: ---------- START MQTT QR LISTENER ----------
echo [4/7] Starting MQTT QR Background Listener...
start /b cmd /c "php artisan mqtt:qrbackgroundlistener >> "%LOG_DIR%\mqtt_qr_%TODAY%.log" 2>&1"

:: ---------- START FRONTEND ----------
echo [5/7] Starting Frontend HTTP Server...
cd /d "%FRONTEND%"
start /b cmd /c "npx http-server dist -p 3000 >> "%LOG_DIR%\frontend_%TODAY%.log" 2>&1"

:: ---------- START CAMERA WATCHER ----------
echo [6/7] Starting NodeJS Camera Watcher...
cd /d "%NODE%"
start /b cmd /c "node watchCameraImages.js >> "%LOG_DIR%\watch_camera_%TODAY%.log" 2>&1"

:: ---------- START FILE ORGANIZER ----------
echo [7/7] Starting NodeJS Organizer...
start /b cmd /c "node organize_files_by_date.js >> "%LOG_DIR%\organize_files_%TODAY%.log" 2>&1"

echo.
echo ==============================================================
echo   ✅ All services launched successfully
echo   📄 Logs available in: %LOG_DIR%
echo ==============================================================
echo.

:: ---------- LIVE LOG VIEW ----------
echo Showing live log output (press CTRL+C to stop)
echo --------------------------------------------------------------
echo.

:live
cls
echo ================== LIVE LOG OUTPUT (%TODAY%) ==================
echo.
type "%LOG_DIR%\mosquitto_%TODAY%.log" 2>nul
type "%LOG_DIR%\laravel_server_%TODAY%.log" 2>nul
type "%LOG_DIR%\queue_worker_%TODAY%.log" 2>nul
type "%LOG_DIR%\mqtt_qr_%TODAY%.log" 2>nul
type "%LOG_DIR%\frontend_%TODAY%.log" 2>nul
type "%LOG_DIR%\watch_camera_%TODAY%.log" 2>nul
type "%LOG_DIR%\organize_files_%TODAY%.log" 2>nul

timeout /t 5 >nul
goto live
