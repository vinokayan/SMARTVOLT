@echo off
setlocal
cd /d "%~dp0"
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0install-smartvolt-ui-v8.ps1" -ProjectRoot "C:\SMARTVOLT"
echo.
pause
