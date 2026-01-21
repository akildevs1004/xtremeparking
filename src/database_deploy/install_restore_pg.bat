@echo off
setlocal EnableExtensions EnableDelayedExpansion
cd /d "%~dp0"

set "PG_MAJOR=16"
set "PG_INSTALLER=postgresql-16.11-2-windows-x64.exe"
set "PG_ROOT=C:\Program Files\PostgreSQL\%PG_MAJOR%"
set "PG_BIN=%PG_ROOT%\bin"
set "PG_SERVICE=postgresql-x64-%PG_MAJOR%"

set "DB_HOST=127.0.0.1"
set "DB_PORT=5432"
set "DB_NAME=xtremeguard_parking"
set "DB_USER=postgres"
set "DB_PASS=postgres"

set "BACKUP_FILE=%~dp0parking_backup.backup"

set "LOG_DIR=%ProgramData%\XtremeGuardParking\logs"
set "LOG_FILE=%LOG_DIR%\db_install.log"

if not exist "%LOG_DIR%" mkdir "%LOG_DIR%" >nul 2>&1

call :log "=================================================="
call :log "[%date% %time%] DB install/restore started"
call :log "WorkingDir: %cd%"

net session >nul 2>&1
if errorlevel 1 (
  call :log "ERROR: Please run this BAT as Administrator."
  echo.
  echo ERROR: Please run as Administrator.
  pause
  exit /b 5
)

call :log "Step: Validations"
if not exist "%PG_INSTALLER%" (
  call :log "ERROR: PostgreSQL installer not found: %PG_INSTALLER%"
  pause
  exit /b 10
)
if not exist "%BACKUP_FILE%" (
  call :log "ERROR: Backup not found: %BACKUP_FILE%"
  pause
  exit /b 11
)

call :log "Step: Check/Install PostgreSQL"
if exist "%PG_BIN%\psql.exe" (
  call :log "PostgreSQL already installed at: %PG_ROOT%"
) else (
  call :log "PostgreSQL not found. Installing silently..."

  REM Correct quoting: start /wait
  start "" /wait "%~dp0%PG_INSTALLER%" ^
    --mode unattended ^
    --unattendedmodeui none ^
    --prefix "%PG_ROOT%" ^
    --superpassword "%DB_PASS%" ^
    --serverport %DB_PORT% >>"%LOG_FILE%" 2>&1

  set "RC=%ERRORLEVEL%"
  call :log "PostgreSQL installer raw exit code: %RC%"

  if "%RC%"=="3010" (
    call :log "Installer requested reboot (3010). Treating as success."
    set "RC=0"
  )

  if not "%RC%"=="0" (
    call :log "ERROR: PostgreSQL installer failed (code %RC%)."
    pause
    exit /b 20
  )

  timeout /t 5 /nobreak >nul
)

call :log "Step: Validate Tools"
if not exist "%PG_BIN%\psql.exe" ( call :log "ERROR: Missing psql.exe at %PG_BIN%\psql.exe" & pause & exit /b 21 )
if not exist "%PG_BIN%\pg_restore.exe" ( call :log "ERROR: Missing pg_restore.exe at %PG_BIN%\pg_restore.exe" & pause & exit /b 22 )
if not exist "%PG_BIN%\createdb.exe" ( call :log "ERROR: Missing createdb.exe at %PG_BIN%\createdb.exe" & pause & exit /b 22 )

call :log "Step: Ensure Service Running"
sc query "%PG_SERVICE%" >>"%LOG_FILE%" 2>&1
if errorlevel 1 (
  call :log "WARNING: Service '%PG_SERVICE%' not found. Detecting..."
  set "DETECTED_SERVICE="
  for /f "tokens=2 delims=:" %%S in ('sc query state^= all ^| findstr /i "SERVICE_NAME: postgresql"') do (
    set "name=%%S"
    set "name=!name: =!"
    echo !name!| findstr /i "postgresql-x64-%PG_MAJOR%" >nul
    if not errorlevel 1 set "DETECTED_SERVICE=!name!"
  )
  if defined DETECTED_SERVICE (
    set "PG_SERVICE=%DETECTED_SERVICE%"
    call :log "Using detected service: %PG_SERVICE%"
  ) else (
    call :log "ERROR: PostgreSQL service not found."
    pause
    exit /b 23
  )
)

net start "%PG_SERVICE%" >>"%LOG_FILE%" 2>&1

call :log "Step: Wait for Port %DB_PORT%"
powershell -NoProfile -Command ^
  "$p=%DB_PORT%; $t=(Get-Date).AddSeconds(60); while((Get-Date) -lt $t){ $c=(Get-NetTCPConnection -LocalPort $p -State Listen -ErrorAction SilentlyContinue | Measure-Object).Count; if($c -gt 0){ exit 0 }; Start-Sleep -Milliseconds 500 }; exit 1" >>"%LOG_FILE%" 2>&1
if errorlevel 1 (
  call :log "ERROR: PostgreSQL did not start listening on port %DB_PORT%."
  pause
  exit /b 24
)

call :log "Step: Create DB if missing"
set "PGPASSWORD=%DB_PASS%"

for /f "usebackq delims=" %%i in (`
  "%PG_BIN%\psql.exe" -h %DB_HOST% -p %DB_PORT% -U %DB_USER% -d postgres -tAc "SELECT 1 FROM pg_database WHERE datname='%DB_NAME%';"
`) do set "DB_EXISTS=%%i"

if "%DB_EXISTS%"=="1" (
  call :log "Database exists: %DB_NAME%"
) else (
  call :log "Creating database: %DB_NAME%"
  "%PG_BIN%\createdb.exe" -h %DB_HOST% -p %DB_PORT% -U %DB_USER% "%DB_NAME%" >>"%LOG_FILE%" 2>&1
  if errorlevel 1 (
    call :log "ERROR: createdb failed (code %ERRORLEVEL%)."
    pause
    exit /b 30
  )
)

call :log "Step: Restore Backup"
call :log "Restoring: %BACKUP_FILE% into %DB_NAME%"

"%PG_BIN%\pg_restore.exe" ^
  -h %DB_HOST% -p %DB_PORT% -U %DB_USER% ^
  -d "%DB_NAME%" ^
  --clean --if-exists ^
  "%BACKUP_FILE%" >>"%LOG_FILE%" 2>&1

if errorlevel 1 (
  call :log "ERROR: pg_restore failed (code %ERRORLEVEL%)."
  pause
  exit /b 40
)

call :log "SUCCESS: Database restore complete."
echo.
echo SUCCESS: Database restore complete.
pause
exit /b 0

:log
echo %~1
echo %~1>>"%LOG_FILE%"
exit /b 0
