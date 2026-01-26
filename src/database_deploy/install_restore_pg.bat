@echo off
setlocal EnableExtensions EnableDelayedExpansion
cd /d "%~dp0"

REM ===================== CONFIG =====================
set "PG_MAJOR=16"
set "PG_INSTALLER=postgresql-16.11-2-windows-x64.exe"
set "PG_ROOT=C:\Program Files\PostgreSQL\%PG_MAJOR%"
set "PG_BIN=%PG_ROOT%\bin"
set "PG_SERVICE=postgresql-x64-%PG_MAJOR%"

set "DB_HOST=127.0.0.1"
set "DB_PORT=5432"
set "DB_NAME=xtremeguard_parking"
set "DB_USER=postgres"
set "DB_PASS=test123"

set "BACKUP_FILE=%~dp0parking_backup.backup"

set "LOG_DIR=%ProgramData%\XtremeGuardParking\logs"
set "LOG_FILE=%LOG_DIR%\db_install.log"
REM ==================================================

if not exist "%LOG_DIR%" mkdir "%LOG_DIR%" >nul 2>&1

call :log "=================================================="
call :log "[%date% %time%] DB install/restore started"
call :log "WorkingDir: %cd%"

REM ---- Require Admin ----
net session >nul 2>&1
if errorlevel 1 (
  call :log "ERROR: Please run this BAT as Administrator."
  echo.
  echo ERROR: Please run as Administrator.
  pause
  exit /b 5
)

call :log "Step: Validations"

REM Backup is required only for first-time restore. If missing, we will still continue if DB already exists.
if not exist "%BACKUP_FILE%" (
  call :log "WARNING: Backup not found: %BACKUP_FILE%"
)

REM Installer is required only if PostgreSQL is not installed. If missing, we will still continue if PG exists.
if not exist "%PG_INSTALLER%" (
  call :log "INFO: PostgreSQL installer not found in script folder: %PG_INSTALLER%"
  call :log "INFO: Will proceed ONLY if PostgreSQL is already installed."
)

REM ===================== DETECT PG =====================
call :log "Step: Detect PostgreSQL tools/service"

set "FOUND_PSQL="

REM 1) Check expected path first
if exist "%PG_BIN%\psql.exe" (
  set "FOUND_PSQL=%PG_BIN%\psql.exe"
  call :log "Found psql at expected path: %FOUND_PSQL%"
)

REM 2) If not found, try PATH
if not defined FOUND_PSQL (
  for %%P in (psql.exe) do (
    if not "%%~$PATH:P"=="" (
      set "FOUND_PSQL=%%~$PATH:P"
      call :log "Found psql in PATH: !FOUND_PSQL!"
    )
  )
)

REM 3) If still not found, try registry to locate installed PG
if not defined FOUND_PSQL (
  for /f "usebackq tokens=2,*" %%A in (`
    reg query "HKLM\SOFTWARE\PostgreSQL\Installations" /s /v "Base Directory" 2^>nul ^| findstr /i "Base Directory"
  `) do (
    set "REG_BASE=%%B"
    set "REG_BASE=!REG_BASE:    =!"
    if exist "!REG_BASE!\bin\psql.exe" (
      set "PG_ROOT=!REG_BASE!"
      set "PG_BIN=!PG_ROOT!\bin"
      set "FOUND_PSQL=!PG_BIN!\psql.exe"
      call :log "Found psql via registry: !FOUND_PSQL!"
      goto :pg_found_done
    )
  )
)
:pg_found_done

REM ---- Detect service name (do NOT error if different name) ----
set "HAS_SERVICE=0"
sc query "%PG_SERVICE%" >nul 2>&1
if not errorlevel 1 (
  set "HAS_SERVICE=1"
) else (
  set "DETECTED_SERVICE="
  for /f "tokens=2 delims=:" %%S in ('sc query state^= all ^| findstr /i "SERVICE_NAME: postgresql"') do (
    set "name=%%S"
    set "name=!name: =!"
    echo !name!| findstr /i "postgresql-x64-%PG_MAJOR%" >nul
    if not errorlevel 1 set "DETECTED_SERVICE=!name!"
  )
  if defined DETECTED_SERVICE (
    set "PG_SERVICE=%DETECTED_SERVICE%"
    set "HAS_SERVICE=1"
    call :log "Using detected service: %PG_SERVICE%"
  ) else (
    call :log "INFO: PostgreSQL service not found yet (may be not installed)."
  )
)

REM ===================== INSTALL IF NEEDED =====================
call :log "Step: Check/Install PostgreSQL"

if defined FOUND_PSQL (
  call :log "PostgreSQL already installed (psql detected). Skipping install."
) else if "%HAS_SERVICE%"=="1" (
  call :log "PostgreSQL service exists (%PG_SERVICE%), but psql not found yet. Will try default PG_BIN: %PG_BIN%"
) else (
  call :log "PostgreSQL not detected. Installing silently..."

  if not exist "%PG_INSTALLER%" (
    call :log "ERROR: Installer missing and PostgreSQL not installed. Cannot continue."
    echo.
    echo ERROR: Installer missing and PostgreSQL not installed. Cannot continue.
    pause
    exit /b 10
  )

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
    echo.
    echo ERROR: PostgreSQL installer failed (code %RC%).
    pause
    exit /b 20
  )

  timeout /t 5 /nobreak >nul

  if exist "%PG_BIN%\psql.exe" set "FOUND_PSQL=%PG_BIN%\psql.exe"
  set "HAS_SERVICE=1"
)

REM Align PG_BIN if psql found elsewhere
if defined FOUND_PSQL (
  for %%D in ("%FOUND_PSQL%") do (
    set "PG_BIN=%%~dpD"
    set "PG_BIN=!PG_BIN:~0,-1!"
  )
)

call :log "PG_BIN resolved to: %PG_BIN%"

call :log "Step: Validate Tools"
if not exist "%PG_BIN%\psql.exe" ( call :log "ERROR: Missing psql.exe at %PG_BIN%\psql.exe" & pause & exit /b 21 )
if not exist "%PG_BIN%\pg_restore.exe" ( call :log "ERROR: Missing pg_restore.exe at %PG_BIN%\pg_restore.exe" & pause & exit /b 22 )
if not exist "%PG_BIN%\createdb.exe" ( call :log "ERROR: Missing createdb.exe at %PG_BIN%\createdb.exe" & pause & exit /b 23 )

REM ===================== START SERVICE =====================
call :log "Step: Ensure Service Running"
sc query "%PG_SERVICE%" >>"%LOG_FILE%" 2>&1
if errorlevel 1 (
  call :log "ERROR: PostgreSQL service '%PG_SERVICE%' not found. Cannot continue."
  echo.
  echo ERROR: PostgreSQL service '%PG_SERVICE%' not found. Cannot continue.
  pause
  exit /b 24
)

net start "%PG_SERVICE%" >>"%LOG_FILE%" 2>&1
REM If it's already running, net start returns errorlevel 2. Treat as OK.
if errorlevel 1 (
  findstr /i /c:"service has already been started" "%LOG_FILE%" >nul 2>&1
  if not errorlevel 1 (
    call :log "Service already running (OK)."
  ) else (
    REM Still might be running; do not stop here—port check will decide.
    call :log "INFO: net start returned non-zero; continuing to port check."
  )
)

call :log "Step: Wait for Port %DB_PORT%"
powershell -NoProfile -Command ^
  "$p=%DB_PORT%; $t=(Get-Date).AddSeconds(60); while((Get-Date) -lt $t){ $c=(Get-NetTCPConnection -LocalPort $p -State Listen -ErrorAction SilentlyContinue | Measure-Object).Count; if($c -gt 0){ exit 0 }; Start-Sleep -Milliseconds 500 }; exit 1" >>"%LOG_FILE%" 2>&1
if errorlevel 1 (
  call :log "ERROR: PostgreSQL did not start listening on port %DB_PORT%."
  echo.
  echo ERROR: PostgreSQL did not start listening on port %DB_PORT%.
  pause
  exit /b 25
)

REM ===================== AUTH FOR PSQL =====================
set "PGPASSWORD=%DB_PASS%"

REM ===================== CHECK DB USER (IDEMPOTENT) =====================
call :log "Step: Check/Create DB user (skip if exists)"

set "USER_EXISTS=0"
for /f "usebackq delims=" %%i in (`
  "%PG_BIN%\psql.exe" -h %DB_HOST% -p %DB_PORT% -U %DB_USER% -d postgres -tAc "SELECT 1 FROM pg_roles WHERE rolname='%DB_USER%';"
`) do (
  set "line=%%i"
  set "line=!line: =!"
  if "!line!"=="1" set "USER_EXISTS=1"
)

if "%USER_EXISTS%"=="1" (
  call :log "DB user exists: %DB_USER% (skip)"
) else (
  call :log "Creating DB user: %DB_USER%"
  "%PG_BIN%\psql.exe" -h %DB_HOST% -p %DB_PORT% -U %DB_USER% -d postgres ^
    -c "CREATE USER %DB_USER% WITH SUPERUSER PASSWORD '%DB_PASS%';" >>"%LOG_FILE%" 2>&1

  REM If user already exists due to race/permission, ignore that specific error and continue
  if errorlevel 1 (
    findstr /i /c:"already exists" "%LOG_FILE%" >nul 2>&1
    if not errorlevel 1 (
      call :log "User already exists (detected in logs). Continuing."
    ) else (
      call :log "WARNING: Create user returned error. Continuing (idempotent mode)."
    )
  )
)

REM ===================== CHECK DB EXISTS (IDEMPOTENT) =====================
call :log "Step: Check/Create DB (skip if exists)"

set "DB_EXISTS=0"
for /f "usebackq delims=" %%i in (`
  "%PG_BIN%\psql.exe" -h %DB_HOST% -p %DB_PORT% -U %DB_USER% -d postgres -tAc "SELECT 1 FROM pg_database WHERE datname='%DB_NAME%';"
`) do (
  set "line=%%i"
  set "line=!line: =!"
  if "!line!"=="1" set "DB_EXISTS=1"
)

if "%DB_EXISTS%"=="1" (
  call :log "Database already exists: %DB_NAME% (skip create + skip restore)"
  goto :install_done
)

call :log "Creating database: %DB_NAME%"
"%PG_BIN%\createdb.exe" -h %DB_HOST% -p %DB_PORT% -U %DB_USER% "%DB_NAME%" >>"%LOG_FILE%" 2>&1

REM createdb may return error if DB exists (race). Do not stop.
if errorlevel 1 (
  findstr /i /c:"already exists" "%LOG_FILE%" >nul 2>&1
  if not errorlevel 1 (
    call :log "Database already exists (detected in logs). Continuing."
    goto :install_done
  ) else (
    call :log "WARNING: createdb returned error. Continuing (idempotent mode)."
  )
)

REM ===================== RESTORE ONLY IF BACKUP EXISTS =====================
call :log "Step: Restore Backup (only if backup exists and DB was newly created)"

if not exist "%BACKUP_FILE%" (
  call :log "WARNING: Backup file missing; skipping restore."
  goto :install_done
)

call :log "Restoring: %BACKUP_FILE% into %DB_NAME%"

"%PG_BIN%\pg_restore.exe" ^
  -h %DB_HOST% -p %DB_PORT% -U %DB_USER% ^
  -d "%DB_NAME%" ^
  --clean --if-exists ^
  "%BACKUP_FILE%" >>"%LOG_FILE%" 2>&1

REM If restore fails, this is a real failure for first-time install
if errorlevel 1 (
  call :log "ERROR: pg_restore failed (code %ERRORLEVEL%)."
  echo.
  echo ERROR: pg_restore failed. Check log: %LOG_FILE%
  pause
  exit /b 40
)

:install_done
call :log "SUCCESS: Installation completed (idempotent checks applied)."
echo.
echo SUCCESS: Installation completed.
echo Log: %LOG_FILE%
pause
exit /b 0

:log
echo %~1
echo %~1>>"%LOG_FILE%"
exit /b 0
