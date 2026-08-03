@echo off
setlocal
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0install-smartvolt-ui-v4-1.ps1" -ProjectRoot "C:\SMARTVOLT"
if errorlevel 1 (
  echo.
  echo Instalasi gagal. Baca pesan error di atas.
  pause
  exit /b 1
)
echo.
echo Instalasi selesai.
pause
