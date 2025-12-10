@echo off
title XtremeParking - Force Stop All Services
echo ===============================================
echo   XtremeParking - Stopping All Services
echo ===============================================
echo.

setlocal ENABLEDELAYEDEXPANSION


REM ------------------------------------------------
REM 1) STOP LARAVEL BACKEND (PORT 8000)
REM ------------------------------------------------
echo Checking port 8000...
set FOUND8000=0
for /f "tokens=5" %%a in ('netstat -ano ^| findstr ":8000" ^| findstr LISTENING') do (
    set FOUND8000=1
    echo   Killing PID %%a (Laravel Backend)
    taskkill /F /PID %%a >nul 2>&1
)
if !FOUND8000!==0 echo   No process running on port 8000.
echo.


REM ------------------------------------------------
REM 2) STOP FRONTEND (PORT 3000)
REM ------------------------------------------------
echo Checking port 3000...
set FOUND3000=0
for /f "tokens=5" %%a in ('netstat -ano ^| findstr ":3000" ^| findstr LISTENING') do (
    set FOUND3000=1
    echo   Killing PID %%a (Frontend)
    taskkill /F /PID %%a >nul 2>&1
)
if !FOUND3000!==0 echo   No process running on port 3000.
echo.



REM ------------------------------------------------
REM 3) STOP CAMERA LIVE STREAM WEBSOCKET PORTS 9991–9999
REM ------------------------------------------------
echo Stopping Camera Live Stream ports (9991–9999)...
for /L %%p in (9991,1,9999) do (
    set FOUNDWS=0
    for /f "tokens=5" %%a in ('netstat -ano ^| findstr ":%%p" ^| findstr LISTENING') do (
        set FOUNDWS=1
        echo   Killing PID %%a (WebSocket Camera Port %%p)
        taskkill /F /PID %%a >nul 2>&1
    )
    if !FOUNDWS!==0 (
        echo   Port %%p free.
    )
)
echo.



REM ------------------------------------------------
REM 4) STOP start_camera_live_stream.js
REM ------------------------------------------------
echo Stopping Camera Live Stream (start_camera_live_stream.js)...
powershell -NoProfile -Command ^
 "Get-CimInstance Win32_Process -Filter \"Name='node.exe'\" | Where-Object { $_.CommandLine -like '*start_camera_live_stream.js*' } | ForEach-Object { Write-Host '  Killing PID' $_.ProcessId; taskkill /F /PID $_.ProcessId }"
echo.



REM ------------------------------------------------
REM 5) STOP watchCameraImages.js
REM ------------------------------------------------
echo Stopping Parking Camera Watcher (watchCameraImages.js)...
powershell -NoProfile -Command ^
 "Get-CimInstance Win32_Process -Filter \"Name='node.exe'\" | Where-Object { $_.CommandLine -like '*watchCameraImages.js*' } | ForEach-Object { Write-Host '  Killing PID' $_.ProcessId; taskkill /F /PID $_.ProcessId }"
echo.



REM ------------------------------------------------
REM 6) STOP organize_files_by_date.js
REM ------------------------------------------------
echo Stopping Parking Organize Camera Images (organize_files_by_date.js)...
powershell -NoProfile -Command ^
 "Get-CimInstance Win32_Process -Filter \"Name='node.exe'\" | Where-Object { $_.CommandLine -like '*organize_files_by_date.js*' } | ForEach-Object { Write-Host '  Killing PID' $_.ProcessId; taskkill /F /PID $_.ProcessId }"
echo.



REM ------------------------------------------------
REM 7) STOP ALL FFMPEG PROCESSES
REM ------------------------------------------------
echo Stopping all FFmpeg processes...
taskkill /F /IM ffmpeg.exe >nul 2>&1
echo.



echo ===============================================
echo     All XtremeParking Services Are Stopped
echo ===============================================
echo.
pause
endlocal
