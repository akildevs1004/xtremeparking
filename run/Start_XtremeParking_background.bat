@echo off
setlocal
title XtremeParking - Supervisor Launcher

set "HERE=%~dp0"
set "PS1=%HERE%background_Start_XtremeParking_Supervisor.ps1"

echo ===============================================
echo  Starting XtremeParking Supervisor
echo  Script: %PS1%
echo ===============================================
echo.

powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%PS1%"

echo.
echo ===============================================
echo  Supervisor stopped or crashed.
echo  Press any key to close this window.
echo ===============================================
pause >nul
endlocal
