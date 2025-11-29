@echo off
title XtremeParking - Service Starter
color 0A

echo ========================================
echo   Starting XtremeParking Services...
echo ========================================
echo.

:: --- Backend Laravel Server ---
cd /d E:\xtremeparking\backend
start "Laravel Server" cmd /k "php artisan serve --host=0.0.0.0"

:: --- Backend Queue Worker ---
cd /d E:\xtremeparking\backend
start "Queue Worker" cmd /k "php artisan queue:work"

:: --- Backend QR Code---
cd /d E:\xtremeparking\backend
start "MQTT QRCode Payments" cmd /k "php artisan mqtt:qrbackgroundlistener"


:: --- Frontend Vue/React Server ---
cd /d E:\xtremeparking\frontend
start "Frontend" cmd /k "npx http-server dist -p 3000"


:: --- Mosquitto MQTT Broker ---
echo Starting Mosquitto MQTT Broker...
start "Mosquitto MQTT" cmd /k ""C:\Program Files\mosquitto\mosquitto.exe" -c "C:\Program Files\mosquitto\mosquitto.conf" -v"



:: --- Backend Queue Worker ---
cd /d E:\xtremeparking\backend
start "Queue Worker" cmd /k "php artisan queue:work"



:: --- NodeJS Camera Watcher ---
cd /d E:\xtremeparking\nodescript
start "Camera Watcher" cmd /k "node watchCameraImages.js"

:: --- NodeJS Organize Camera Images ---
cd /d E:\xtremeparking\nodescript
start "Organize Camera Images" cmd /k "node organize_files_by_date.js"

echo.
echo All services launched successfully!
echo.
pause
exit
