@echo off
setlocal enabledelayedexpansion

title XtremeParking - Service Starter
color 0A

echo ========================================
echo   Starting XtremeParking Services...
echo ========================================
echo.

REM ==================================================
REM PATH CONFIGURATION
REM ==================================================
set "BACKEND=D:\projects\vehicleparkingbills\xtremeparking\backend"
set "NODESCRIPT=D:\projects\vehicleparkingbills\xtremeparking\nodescript"
set "MOSQUITTO_EXE=C:\Program Files\mosquitto\mosquitto.exe"
set "MOSQUITTO_CONF=C:\Program Files\mosquitto\mosquitto.conf"

REM ==================================================
REM LARAVEL SERVER
REM ==================================================
cd /d "%BACKEND%"
start "Laravel Server" cmd /k "php artisan serve --host=0.0.0.0 --port=8000"
timeout /t 20 /nobreak

REM ==================================================
REM QUEUE WORKER
REM ==================================================
cd /d "%BACKEND%"
start "Queue Worker" cmd /k "php artisan queue:work --tries=3 --timeout=120"
timeout /t 5 /nobreak

REM ==================================================
REM MQTT QR CODE PAYMENT LISTENER
REM ==================================================
cd /d "%BACKEND%"
start "MQTT QRCode Payments" cmd /k "php artisan mqtt:qrbackgroundlistener"
timeout /t 5 /nobreak

REM ==================================================
REM ORGANIZE FILES WATCHER
REM ==================================================
cd /d "%NODESCRIPT%"
start "Organize Files Watcher" cmd /k "node organize_files_by_date.js"
timeout /t 5 /nobreak

REM ==================================================
REM CAMERA Images 
REM ==================================================
cd /d "%NODESCRIPT%"
start "Camera Images" cmd /k "node watchCameraImages.js"
timeout /t 10 /nobreak

REM ==================================================
REM CAMERA WATCHER
REM ==================================================
cd /d "%NODESCRIPT%"
start "Live Camera Stream" cmd /k "node start_camera_live_stream.js"
timeout /t 5 /nobreak



REM ==================================================
REM MOSQUITTO MQTT BROKER (FOREGROUND - BLOCKING)
REM ==================================================
echo.
echo ========================================
echo   Starting Mosquitto MQTT (Foreground)
echo   Press CTRL+C to stop everything
echo ========================================
echo.

"%MOSQUITTO_EXE%" -c "%MOSQUITTO_CONF%" -v

REM ==================================================
REM EXECUTION NEVER REACHES HERE UNTIL MOSQUITTO STOPS
REM ==================================================
echo Mosquitto stopped.
pause
exit /b
