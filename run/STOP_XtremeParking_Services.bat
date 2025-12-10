@echo off
title XtremeParking - Force Stop All Services
echo ===============================================
echo   XtremeParking - Stopping All Services
echo ===============================================
echo.

setlocal ENABLEDELAYEDEXPANSION

REM ------------------------------------------------
REM 1) Kill anything listening on port 8000 (Laravel)
REM ------------------------------------------------
echo Checking port 8000...
set "FOUND8000=0"
for /f "tokens=5" %%a in ('netstat -ano ^| findstr ":8000" ^| findstr LISTENING') do (
    set "FOUND8000=1"
    echo   Killing PID %%a on port 8000
    taskkill /F /PID %%a >nul 2>&1
)
if !FOUND8000! EQU 0 (
    echo   No process found listening on port 8000.
)
echo.

REM ------------------------------------------------
REM 2) Kill anything listening on port 3000 (Frontend)
REM ------------------------------------------------
echo Checking port 3000...
set "FOUND3000=0"
for /f "tokens=5" %%a in ('netstat -ano ^| findstr ":3000" ^| findstr LISTENING') do (
    set "FOUND3000=1"
    echo   Killing PID %%a on port 3000
    taskkill /F /PID %%a >nul 2>&1
)
if !FOUND3000! EQU 0 (
    echo   No process found listening on port 3000.
)
echo.

REM ------------------------------------------------
REM 3) Stop Parking Camera Watcher (watchCameraImages.js)
REM ------------------------------------------------
echo Stopping Parking Camera Watcher (watchCameraImages.js)...
set "FOUND_WATCHER=0"
for /f %%p in ('
  powershell -NoProfile -Command ^
    "Get-CimInstance Win32_Process -Filter \"Name = 'node.exe'\" | Where-Object { $_.CommandLine -like '*watchCameraImages.js*' } | ForEach-Object ProcessId"
') do (
    set "FOUND_WATCHER=1"
    echo   Killing node PID %%p for watchCameraImages.js
    taskkill /F /PID %%p >nul 2>&1
)
if !FOUND_WATCHER! EQU 0 (
    echo   No node.exe found running watchCameraImages.js
)
echo.

REM ------------------------------------------------
REM 4) Stop Parking Organize Camera Images (organize_files_by_date.js)
REM ------------------------------------------------
echo Stopping Parking Organize Camera Images (organize_files_by_date.js)...
set "FOUND_ORG=0"
for /f %%p in ('
  powershell -NoProfile -Command ^
    "Get-CimInstance Win32_Process -Filter \"Name = 'node.exe'\" | Where-Object { $_.CommandLine -like '*organize_files_by_date.js*' } | ForEach-Object ProcessId"
') do (
    set "FOUND_ORG=1"
    echo   Killing node PID %%p for organize_files_by_date.js
    taskkill /F /PID %%p >nul 2>&1
)
if !FOUND_ORG! EQU 0 (
    echo   No node.exe found running organize_files_by_date.js
)
echo.

echo ===============================================
echo    All XtremeParking services stopped:
echo      - Port 8000 (Laravel backend)
echo      - Port 3000 (Frontend)
echo      - watchCameraImages.js (Camera watcher)
echo      - organize_files_by_date.js (Organizer)
echo ===============================================
echo.
pause
endlocal
