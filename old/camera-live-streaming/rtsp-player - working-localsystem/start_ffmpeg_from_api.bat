@echo off
title Start FFmpeg for all cameras (via Laravel API)

REM ========= CONFIG =========
REM Laravel API that returns { data: [ { id, name, rtsp_url }, ... ] }
set CAMERA_API_URL=http://127.0.0.1:8000/api/parking-cameras?company_id=8&login_user_id=1875&login_user_type=company

REM Base HTTP port for FFmpeg pushes (8081,8082,8083,...)
set BASE_HTTP_PORT=8081

REM FFmpeg executable (or full path)
set FFMPEG_EXE=ffmpeg

echo Fetching cameras from Laravel and starting FFmpeg...
echo API: %CAMERA_API_URL%
echo.

REM ========= CALL POWERSHELL TO DO THE WORK =========
powershell -NoProfile -ExecutionPolicy Bypass ^
  "$api = '%CAMERA_API_URL%';" ^
  "$basePort = %BASE_HTTP_PORT%;" ^
  "$ffmpeg = '%FFMPEG_EXE%';" ^
  "try {" ^
  "  $resp = Invoke-RestMethod -Uri $api -UseBasicParsing;" ^
  "  $cams = $resp.data;" ^
  "  if(-not $cams -or $cams.Count -eq 0) { Write-Host 'No cameras received from API.'; exit 1 }" ^
  "  Write-Host 'Received' $cams.Count 'cameras. Starting FFmpeg...';" ^
  "  for($i = 0; $i -lt $cams.Count; $i++) {" ^
  "    $cam = $cams[$i];" ^
  "    $port = $basePort + $i;" ^
  "    $rtsp = $cam.rtsp_url;" ^
  "    $name = $cam.name;" ^
  "    $pushUrl = 'http://127.0.0.1:' + $port + '/stream';" ^
  "    Write-Host '--------------------------------------------------';" ^
  "    Write-Host 'Camera #' ($i+1) 'ID=' $cam.id 'Name=' $name;" ^
  "    Write-Host 'RTSP   :' $rtsp;" ^
  "    Write-Host 'PUSH   :' $pushUrl;" ^
  "    Write-Host 'Starting FFmpeg process in background...';" ^
  "    Start-Process $ffmpeg -ArgumentList @('-rtsp_transport','tcp','-i',$rtsp,'-f','mpegts','-codec:v','mpeg1video','-s','1280x720','-b:v','2000k','-bf','0','-r','25',$pushUrl) -WindowStyle Minimized;" ^
  "  }" ^
  "} catch { Write-Host 'Error calling API:' $_.Exception.Message; exit 1 }"

echo.
echo All FFmpeg processes started (one per camera).
echo You can close this window; FFmpeg will remain running.
pause
