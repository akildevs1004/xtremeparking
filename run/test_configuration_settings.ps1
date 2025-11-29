<# 
  verify_xtremeparking_setup.ps1

  Verifies:
  - PostgreSQL installation & basic connectivity
  - PHP + extensions + PATH
  - Laravel backend folder & artisan
  - Node.js + nodescript deps + .env
  - Mosquitto config (listeners + allow_anonymous)
  - Frontend dist build
  - Camera image folder structure
  - Backend & frontend .env MQTT settings
  - Camera URLs reachability
  - Sample vehicle image filename pattern

  Run:  PowerShell (Admin)
        Set-ExecutionPolicy RemoteSigned -Scope Process
        .\verify_xtremeparking_setup.ps1
#>

param(
    [string]$BackendPath        = "D:\xtremeparking\backend",
    [string]$FrontendPath       = "D:\xtremeparking\frontend",
    [string]$NodeScriptPath     = "D:\xtremeparking\nodescript",
    [string]$PostgresBinPath    = "C:\Program Files\PostgreSQL\17\bin",
    [string]$MosquittoConfPath  = "C:\Program Files\mosquitto\mosquitto.conf",
    [string]$PhpPath            = "C:\php",
    [string]$ImageRootPath      = "D:\parking_camera_logs",
    [string]$CameraCompanyId    = "8",
    [string]$SftpUser           = "cameralog",
    [string]$Camera1Url         = "http://192.168.88.154/",
    [string]$Camera2Url         = "http://192.168.88.153/doc/page/preview.asp"
)

# ---------- Helper functions ----------

function Write-Section {
    param([string]$Text)
    Write-Host ""
    Write-Host "==== $Text ====" -ForegroundColor Cyan
}

function Write-OK {
    param([string]$Text)
    Write-Host "  [OK]  $Text" -ForegroundColor Green
}

function Write-Warn {
    param([string]$Text)
    Write-Host "  [WARN] $Text" -ForegroundColor Yellow
}

function Write-Err {
    param([string]$Text)
    Write-Host "  [FAIL] $Text" -ForegroundColor Red
}

# ---------- 1. PostgreSQL ----------

Write-Section "1. PostgreSQL"

$pgService = Get-Service | Where-Object { $_.Name -like "postgresql*" }
if ($pgService) {
    Write-OK "PostgreSQL service found: $($pgService.Name) (Status: $($pgService.Status))"
} else {
    Write-Err "PostgreSQL service not found. Check installation."
}

$psqlExe = Join-Path $PostgresBinPath "psql.exe"
if (Test-Path $psqlExe) {
    Write-OK "psql found at $psqlExe"
    try {
        $env:PGPASSWORD = "welcome"
        $psqlOutput = & $psqlExe -U postgres -h 127.0.0.1 -p 5432 -d postgres -c "SELECT 1;" 2>&1
        if ($LASTEXITCODE -eq 0) {
            Write-OK "PostgreSQL connection (user=postgres / password=welcome) succeeded."
        } else {
            Write-Err "psql connection failed. Output: $psqlOutput"
        }
        Remove-Item Env:\PGPASSWORD -ErrorAction SilentlyContinue
    } catch {
        Write-Err "Error running psql: $($_.Exception.Message)"
    }
} else {
    Write-Warn "psql.exe not found at $PostgresBinPath. Adjust PostgresBinPath parameter if needed."
}

# ---------- 2. PHP & Laravel backend ----------

Write-Section "2. PHP & Laravel Backend"

$phpExe = Join-Path $PhpPath "php.exe"
if (Test-Path $phpExe) {
    Write-OK "PHP executable found at $phpExe"
    $phpVersion = & $phpExe -v 2>$null | Select-Object -First 1
    Write-OK "PHP version: $phpVersion"
} else {
    Write-Err "PHP not found in $PhpPath"
}

# Check PATH contains C:\php
if ($env:Path -split ";" | Where-Object { $_ -eq $PhpPath }) {
    Write-OK "PATH contains $PhpPath"
} else {
    Write-Warn "PATH does not contain $PhpPath. Command 'php' may not work globally."
}

# Check PHP extensions
if (Test-Path $phpExe) {
    $modules = & $phpExe -m 2>$null
    if ($modules -contains "pdo_pgsql") {
        Write-OK "PHP extension 'pdo_pgsql' enabled."
    } else {
        Write-Err "PHP extension 'pdo_pgsql' NOT enabled. Add 'extension=pdo_pgsql' to php.ini."
    }

    if ($modules -contains "fileinfo") {
        Write-OK "PHP extension 'fileinfo' enabled."
    } else {
        Write-Err "PHP extension 'fileinfo' NOT enabled. Add 'extension=fileinfo' to php.ini."
    }
}

# Laravel backend folder
if (Test-Path $BackendPath) {
    Write-OK "Backend folder exists: $BackendPath"
    if (Test-Path (Join-Path $BackendPath "artisan")) {
        Write-OK "artisan file found in backend."
        Push-Location $BackendPath
        try {
            $artisanVersion = & $phpExe artisan --version 2>&1
            if ($LASTEXITCODE -eq 0) {
                Write-OK "Laravel artisan responds: $artisanVersion"
            } else {
                Write-Err "php artisan failed: $artisanVersion"
            }
        } catch {
            Write-Err "Error running php artisan: $($_.Exception.Message)"
        }
        Pop-Location
    } else {
        Write-Err "artisan not found in backend folder."
    }
} else {
    Write-Err "Backend folder not found: $BackendPath"
}

# ---------- 3. Node.js & nodescript ----------

Write-Section "3. Node.js & nodescript"

# Check node
$nodeVersion = & node -v 2>$null
if ($LASTEXITCODE -eq 0 -and $nodeVersion) {
    Write-OK "Node.js is installed: $nodeVersion"
} else {
    Write-Err "Node.js not found in PATH."
}

if (Test-Path $NodeScriptPath) {
    Write-OK "nodescript folder exists: $NodeScriptPath"
    $nodeModulesPath = Join-Path $NodeScriptPath "node_modules"
    if (Test-Path $nodeModulesPath) {
        $npmPkgs = @("express","pg","cors","dotenv","mqtt","chokidar","axios")
        foreach ($pkg in $npmPkgs) {
            if (Test-Path (Join-Path $nodeModulesPath $pkg)) {
                Write-OK "npm package '$pkg' installed."
            } else {
                Write-Err "npm package '$pkg' NOT found. Run 'npm install $pkg' in $NodeScriptPath."
            }
        }
    } else {
        Write-Err "node_modules folder not found in nodescript. Run 'npm install' there."
    }

    # .env file
    $nodeEnvPath = Join-Path $NodeScriptPath ".env"
    if (Test-Path $nodeEnvPath) {
        Write-OK ".env file found in nodescript."
        $envContent = Get-Content $nodeEnvPath
        $requiredNodeEnvKeys = @("DB_HOST","DB_PORT","DB_DATABASE","DB_USERNAME","DB_PASSWORD","MQTT_HOST","MQTT_PORT")
        foreach ($key in $requiredNodeEnvKeys) {
            if ($envContent -match ("^$key\s*=")) {
                Write-OK "nodescript .env has key: $key"
            } else {
                Write-Warn "nodescript .env is missing key: $key"
            }
        }
    } else {
        Write-Err ".env file NOT found in nodescript folder."
    }

    # Check watchCameraImages.js
    $watchScript = Join-Path $NodeScriptPath "watchCameraImages.js"
    if (Test-Path $watchScript) {
        Write-OK "watchCameraImages.js exists."
    } else {
        Write-Err "watchCameraImages.js NOT found in nodescript."
    }
} else {
    Write-Err "nodescript folder not found: $NodeScriptPath"
}

# ---------- 4. Mosquitto ----------

Write-Section "4. Mosquitto MQTT"

$mosqService = Get-Service -ErrorAction SilentlyContinue | Where-Object { $_.Name -like "mosquitto*" }
if ($mosqService) {
    Write-OK "Mosquitto service found: $($mosqService.Name) (Status: $($mosqService.Status))"
} else {
    Write-Err "Mosquitto service not found."
}

if (Test-Path $MosquittoConfPath) {
    Write-OK "mosquitto.conf found at $MosquittoConfPath"
    $conf = Get-Content $MosquittoConfPath

    if ($conf -match "allow_anonymous\s+true") {
        Write-OK "allow_anonymous true is set."
    } else {
        Write-Err "allow_anonymous true NOT found in mosquitto.conf"
    }

    if ($conf -match "listener\s+1883\s+0\.0\.0\.0" -and $conf -match "protocol\s+mqtt") {
        Write-OK "TCP listener 1883 on 0.0.0.0 with protocol mqtt configured."
    } else {
        Write-Err "TCP MQTT listener 1883 0.0.0.0 NOT properly configured."
    }

    if ($conf -match "listener\s+8083\s+0\.0\.0\.0" -and $conf -match "protocol\s+websockets") {
        Write-OK "WebSocket listener 8083 on 0.0.0.0 with protocol websockets configured."
    } else {
        Write-Err "WebSocket listener 8083 0.0.0.0 NOT properly configured."
    }
} else {
    Write-Err "mosquitto.conf not found at $MosquittoConfPath"
}

# ---------- 5. Frontend ----------

Write-Section "5. Frontend"

if (Test-Path $FrontendPath) {
    Write-OK "Frontend folder exists: $FrontendPath"
    $distPath = Join-Path $FrontendPath "dist"
    if (Test-Path $distPath) {
        Write-OK "dist folder exists (build present)."
    } else {
        Write-Err "dist folder NOT found. Run 'npm run build' in frontend."
    }
} else {
    Write-Err "Frontend folder not found: $FrontendPath"
}

# ---------- 6. Camera image folder & SFTP user ----------

Write-Section "6. Camera Image Folder & SFTP User"

if (Test-Path $ImageRootPath) {
    Write-OK "Image root folder exists: $ImageRootPath"
    $companyFolder = Join-Path $ImageRootPath $CameraCompanyId
    if (Test-Path $companyFolder) {
        Write-OK "Company image folder exists: $companyFolder"
    } else {
        Write-Err "Company image folder does NOT exist: $companyFolder"
    }
} else {
    Write-Err "Image root folder does NOT exist: $ImageRootPath"
}

try {
    $localUser = Get-LocalUser -Name $SftpUser -ErrorAction Stop
    Write-OK "SFTP Windows user '$SftpUser' exists."
} catch {
    Write-Warn "SFTP Windows user '$SftpUser' NOT found."
}

# ---------- 7. Backend .env MQTT ----------

Write-Section "7. Backend .env (MQTT)"

$backendEnvPath = Join-Path $BackendPath ".env"
if (Test-Path $backendEnvPath) {
    Write-OK ".env file found in backend."
    $backendEnv = Get-Content $backendEnvPath
    $requiredBackendKeys = @("MQTT_HOST","MQTT_PORT","MQTT_WEBSOCKET_PORT")
    foreach ($key in $requiredBackendKeys) {
        if ($backendEnv -match ("^$key\s*=")) {
            $val = ($backendEnv | Where-Object { $_ -match "^$key\s*=" }) -replace "^$key\s*=\s*",""
            Write-OK "backend .env $key = $val"
        } else {
            Write-Err "backend .env is missing key: $key"
        }
    }
} else {
    Write-Err ".env not found in backend: $BackendPath"
}

# ---------- 8. Frontend .env MQTT ----------

Write-Section "8. Frontend .env (MQTT)"

$frontendEnvPath = Join-Path $FrontendPath ".env"
if (Test-Path $frontendEnvPath) {
    Write-OK ".env file found in frontend."
    $frontendEnv = Get-Content $frontendEnvPath
    $requiredFrontKeys = @("VITE_MQTT_HOST","VITE_MQTT_PORT","VITE_MQTT_PROTOCOL")
    foreach ($key in $requiredFrontKeys) {
        if ($frontendEnv -match ("^$key\s*=")) {
            $val = ($frontendEnv | Where-Object { $_ -match "^$key\s*=" }) -replace "^$key\s*=\s*",""
            Write-OK "frontend .env $key = $val"
        } else {
            Write-Err "frontend .env is missing key: $key"
        }
    }
} else {
    Write-Warn ".env not found in frontend folder (might be using other env files)."
}

# ---------- 9. Camera URLs ----------

Write-Section "9. Camera URLs Reachability"

function Test-Url {
    param([string]$Url)
    try {
        $resp = Invoke-WebRequest -Uri $Url -UseBasicParsing -TimeoutSec 5
        if ($resp.StatusCode -ge 200 -and $resp.StatusCode -lt 400) {
            Write-OK "Camera URL reachable: $Url (Status: $($resp.StatusCode))"
        } else {
            Write-Warn "Camera URL responded with HTTP $($resp.StatusCode): $Url"
        }
    } catch {
        Write-Err "Failed to reach $Url : $($_.Exception.Message)"
    }
}

Test-Url -Url $Camera1Url
Test-Url -Url $Camera2Url

# ---------- 10. Vehicle image filename pattern ----------

Write-Section "10. Vehicle Image Filename Pattern"

if (Test-Path $ImageRootPath) {
    $pattern = "*_VEHICLE_DETECTION_*.JPG"
    $files = Get-ChildItem -Path $ImageRootPath -Recurse -Filter $pattern -ErrorAction SilentlyContinue | Select-Object -First 5
    if ($files) {
        Write-OK "Found vehicle image files matching pattern '$pattern':"
        foreach ($f in $files) {
            Write-Host "      $($f.FullName)"
        }
    } else {
        Write-Warn "No files matching pattern '$pattern' found under $ImageRootPath"
    }
} else {
    Write-Warn "Image root path not found; skipping image pattern check."
}

Write-Host ""
Write-Host "Verification completed." -ForegroundColor Cyan
