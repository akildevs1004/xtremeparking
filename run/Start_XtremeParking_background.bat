@echo off
set "HERE=%~dp0"
powershell -NoExit -ExecutionPolicy Bypass -File "%HERE%background_Start_XtremeParking_Supervisor.ps1"

echo.
echo ===============================================
echo    All XtremeParking Services Started
echo ===============================================
echo.
pause
