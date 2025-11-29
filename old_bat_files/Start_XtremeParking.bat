@echo off
set "HERE=%~dp0"
powershell -NoExit -ExecutionPolicy Bypass -File "%HERE%Start_XtremeParking.ps1"
