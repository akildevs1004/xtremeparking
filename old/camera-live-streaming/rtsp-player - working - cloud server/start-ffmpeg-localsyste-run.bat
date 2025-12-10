@echo off
set FFMPEG=ffmpeg

set RTSP_URL=rtsp://admin:hik@1234@192.168.2.218:554/Streaming/Channels/101
set STREAM_URL=https://parking.xtremeguard.org/stream

"%FFMPEG%" -rtsp_transport tcp -i "%RTSP_URL%" ^
  -f mpegts -codec:v mpeg1video -s 1280x720 -b:v 2000k -bf 0 -r 25 ^
  "%STREAM_URL%"

pause
