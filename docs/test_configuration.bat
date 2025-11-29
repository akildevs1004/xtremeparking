@echo off
cls
echo ===============================================
echo   XtremeParking - System Verification Tool
echo ===============================================
echo.

:: Set the path of the PowerShell script
set SCRIPT_PATH=%~dp0test_configuration_settings.ps1

:: Check if the ps1 exists
if not exist "%SCRIPT_PATH%" (
    echo ERROR: PowerShell script not found!
    echo Expected at: %SCRIPT_PATH%
    pause
    exit /b
)

echo Running PowerShell script...
echo.

:: Run PowerShell with required permissions
powershell -NoProfile -ExecutionPolicy Bypass -File "%SCRIPT_PATH%"

echo.
echo ===============================================
echo Verification Completed.
echo ===============================================
pause
