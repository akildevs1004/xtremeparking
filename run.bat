@echo off
title XtremeParking - Service Starter
color 0A

echo ========================================
echo   Starting XtremeParking Services...
echo ========================================
echo.

:: --- Backend Laravel Server ---
::cd /d E:\xtremeparking\backend
::start "Laravel Server" cmd /k "php artisan serve --host=0.0.0.0  "

:: --- Backend Queue Worker ---
cd /d D:\projects\vehicleparkingbills\xtremeparking\backend
start "Queue Worker" cmd /k "php artisan queue:work"


:: --- Backend Queue Worker ---
cd /d D:\projects\vehicleparkingbills\xtremeparking\backend
start "MQTT QRCode Payments" cmd /k "php artisan mqtt:qrbackgroundlistener"



:: --- Frontend Vue/React Server ---
::cd /d E:\xtremeparking\frontend

::start "Frontend" cmd /k "npx http-server dist -p 3000"
::start "Frontend" cmd /k "serve dist"


:: --- NodeJS Camera Watcher ---
cd /d D:\projects\vehicleparkingbills\xtremeparking\nodescript
start "Camera Watcher" cmd /k "node watchCameraImages.js"

:: --- Mosquitto MQTT Broker ---
echo Starting Mosquitto MQTT Broker...
start "Mosquitto MQTT" cmd /k ""C:\Program Files\mosquitto\mosquitto.exe" -c "C:\Program Files\mosquitto\mosquitto.conf" -v"


:: --- Organize Files Watcher ---
::cd /d D:\projects\vehicleparkingbills\xtremeparking\nodescript
::start "Organize Files Watcher" cmd /k "node organize_files_by_date.js"
 

echo.
echo All services launched successfully!
echo.
pause
exit
