@echo off
setlocal
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0install-smartvolt-ui-v5.ps1" -ProjectRoot "C:\SMARTVOLT"
if errorlevel 1 (
    echo.
    echo Instalasi gagal. Periksa pesan di atas.
    pause
    exit /b 1
)
echo.
echo Instalasi selesai.
pause
