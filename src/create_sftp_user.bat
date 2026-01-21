@echo off
setlocal EnableExtensions EnableDelayedExpansion

REM ==================================================
REM ONE-TIME EXECUTION FLAG
REM ==================================================
set "FLAG_DIR=C:\ProgramData\XtremeGuard"
set "FLAG_FILE=%FLAG_DIR%\sftp_installed.flag"

if exist "%FLAG_FILE%" (
    echo SFTP setup already completed. Skipping.
    exit /b 0
)

REM ==================================================
REM CONFIGURATION
REM ==================================================
set "SFTP_USER=sfptuser"
set "SFTP_PASS=password"

REM INSTALL_DIR passed from NSIS as %1 (recommended)
set "INSTALL_DIR=%~1"
if "%INSTALL_DIR%"=="" set "INSTALL_DIR=%~dp0"

REM Ensure INSTALL_DIR ends with backslash
if not "%INSTALL_DIR:~-1%"=="\" set "INSTALL_DIR=%INSTALL_DIR%\"

set "SFTP_ROOT=%INSTALL_DIR%parking_camera_logs"
set "SFTP_DATA=%SFTP_ROOT%\data"

echo.
echo =====================================
echo   ONE-TIME SFTP SETUP STARTED
echo =====================================
echo Install Dir: %INSTALL_DIR%
echo Chroot Dir : %SFTP_ROOT%
echo Writable   : %SFTP_DATA%
echo.

REM ==================================================
REM INSTALL OPENSSH (SAFE IF EXISTS)
REM ==================================================
echo Checking OpenSSH Server...
powershell -Command "Add-WindowsCapability -Online -Name OpenSSH.Server~~~~0.0.1.0" >nul 2>&1

sc config sshd start=auto >nul 2>&1
net start sshd >nul 2>&1

REM ==================================================
REM CREATE FOLDERS (IGNORE IF EXISTS)
REM ==================================================
echo Creating folders (if missing)...
if not exist "%SFTP_ROOT%" mkdir "%SFTP_ROOT%" >nul 2>&1
if not exist "%SFTP_DATA%" mkdir "%SFTP_DATA%" >nul 2>&1

REM ==================================================
REM CREATE USER (IGNORE IF EXISTS)
REM ==================================================
echo Checking SFTP user...
net user "%SFTP_USER%" >nul 2>&1
if %errorLevel% neq 0 (
    echo Creating SFTP user...
    net user "%SFTP_USER%" "%SFTP_PASS%" /add >nul
    net localgroup Users "%SFTP_USER%" /add >nul
    wmic useraccount where name='%SFTP_USER%' set PasswordExpires=FALSE >nul
) else (
    echo User already exists. Skipping user creation.
)

REM ==================================================
REM APPLY PERMISSIONS (SAFE TO RE-APPLY)
REM ==================================================
echo Applying permissions...

REM Chroot root (NO WRITE for user)
icacls "%SFTP_ROOT%" /inheritance:r >nul
icacls "%SFTP_ROOT%" /grant "SYSTEM:(OI)(CI)F" >nul
icacls "%SFTP_ROOT%" /grant "Administrators:(OI)(CI)F" >nul
icacls "%SFTP_ROOT%" /remove "%SFTP_USER%" >nul 2>&1

REM Writable data folder
icacls "%SFTP_DATA%" /inheritance:r >nul
icacls "%SFTP_DATA%" /grant "SYSTEM:(OI)(CI)F" >nul
icacls "%SFTP_DATA%" /grant "Administrators:(OI)(CI)F" >nul
icacls "%SFTP_DATA%" /grant "%SFTP_USER%:(OI)(CI)M" >nul

REM ==================================================
REM CONFIGURE OPENSSH (APPEND ONLY ONCE)
REM ==================================================
set "SSHD_CONFIG=C:\ProgramData\ssh\sshd_config"

if not exist "%SSHD_CONFIG%" (
    echo ERROR: sshd_config not found at %SSHD_CONFIG%
    exit /b 2
)

findstr /C:"Match User %SFTP_USER%" "%SSHD_CONFIG%" >nul
if %errorLevel% neq 0 (
    echo Updating sshd_config...

    REM Add subsystem ONLY if missing (safer)
    findstr /R /C:"^[ ]*Subsystem[ ]\+sftp[ ]\+internal-sftp" "%SSHD_CONFIG%" >nul
    if %errorLevel% neq 0 (
        echo.>>"%SSHD_CONFIG%"
        echo Subsystem sftp internal-sftp>>"%SSHD_CONFIG%"
    )

    echo.>>"%SSHD_CONFIG%"
    echo Match User %SFTP_USER%>>"%SSHD_CONFIG%"
    echo     ForceCommand internal-sftp>>"%SSHD_CONFIG%"
    echo     PasswordAuthentication yes>>"%SSHD_CONFIG%"
    echo     ChrootDirectory %SFTP_ROOT%>>"%SSHD_CONFIG%"
    echo     AllowTcpForwarding no>>"%SSHD_CONFIG%"
    echo     X11Forwarding no>>"%SSHD_CONFIG%"
) else (
    echo sshd_config already configured. Skipping.
)

net stop sshd >nul 2>&1
net start sshd >nul 2>&1

REM ==================================================
REM MARK INSTALL COMPLETE
REM ==================================================
if not exist "%FLAG_DIR%" mkdir "%FLAG_DIR%" >nul 2>&1
echo INSTALLED > "%FLAG_FILE%"

echo.
echo =====================================
echo   SFTP SETUP COMPLETED SUCCESSFULLY
echo =====================================
echo Username : %SFTP_USER%
echo Chroot   : %SFTP_ROOT%
echo Writable : /data
echo Port     : 22
echo.
exit /b 0
