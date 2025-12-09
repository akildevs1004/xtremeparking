@echo off
title RTSP Streaming System - Full Auto Start

echo ==============================================
echo   STARTING FULL RTSP STREAMING SYSTEM
echo ==============================================
echo.

REM --------- SET PATHS ---------
set NODE=node
set FFMPEG=ffmpeg
set WEB_PORT=3333
set RTSP_URL=rtsp://admin:hik@1234@192.168.2.218:554/Streaming/Channels/101
set STREAM_URL=http://127.0.0.1:8081/stream


REM --------- START NODE server.js (WebSocket Relay) ---------
echo Starting Node Relay Server: server.js
start "Node Relay Server" cmd /k "%NODE% server.js"
timeout /t 2 >nul


REM --------- START webserver.js (LAN Web Server) ---------
echo Starting Web Server: webserver.js
start "Web Server" cmd /k "%NODE% webserver.js"
timeout /t 3 >nul


REM --------- FIND LOCAL LAN IP ---------
for /f "tokens=2 delims=:" %%a in ('ipconfig ^| findstr /c:"IPv4 Address"') do (
    set LAN_IP=%%a
)
set LAN_IP=%LAN_IP: =%


echo.
echo Your RTSP Player is available on LAN at:
echo   http://%LAN_IP%:%WEB_PORT%
echo.
 

REM --------- START FFMPEG AUTO-RESTART LOOP ---------
echo Starting FFmpeg Auto-Restart
echo ==============================================
:ffloop
echo.
echo [FFMPEG] Starting at %date% %time%
"%FFMPEG%" -rtsp_transport tcp -i "%RTSP_URL%" -f mpegts -codec:v mpeg1video -s 1280x720 -b:v 2000k -bf 0 -r 25 "%STREAM_URL%"

echo.
echo [FFMPEG] Exited with error %errorlevel%
echo Restarting in 5 seconds...
timeout /t 5 /nobreak >nul
goto ffloop
