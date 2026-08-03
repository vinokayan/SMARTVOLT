param(
    [string]$ProjectRoot = "C:\SMARTVOLT"
)

$ErrorActionPreference = "Stop"
$PatchRoot = Split-Path -Parent $MyInvocation.MyCommand.Path
$ProjectRoot = [System.IO.Path]::GetFullPath($ProjectRoot)
$Timestamp = Get-Date -Format "yyyyMMdd-HHmmss"
$BackupRoot = Join-Path $ProjectRoot "storage\ui-backups\v4-1-$Timestamp"

if (-not (Test-Path (Join-Path $ProjectRoot "artisan"))) {
    throw "Folder Laravel tidak ditemukan: $ProjectRoot"
}

$Files = @(
    "resources\views\layouts\app.blade.php",
    "resources\views\components\icon.blade.php",
    "resources\views\components\notification-bell.blade.php",
    "resources\views\dashboard.blade.php",
    "resources\views\rooms.blade.php",
    "resources\views\rooms-show.blade.php",
    "resources\views\devices.blade.php",
    "resources\views\settings\index.blade.php",
    "resources\views\auth\energy-history.blade.php",
    "resources\views\auth\login.blade.php",
    "resources\views\auth\register.blade.php",
    "resources\views\auth\forgot-password.blade.php",
    "resources\views\auth\reset_password.blade.php",
    "resources\views\auth\logout.blade.php",
    "resources\views\welcome.blade.php",
    "public\assets\css\smartvolt-app.css",
    "public\assets\css\smartvolt-v4.css",
    "public\assets\css\smartvolt-auth.css",
    "public\assets\css\smartvolt-auth-v4.css",
    "public\assets\css\smartvolt-login.css",
    "public\assets\js\chart.umd.min.js",
    "public\assets\js\smartvolt-app.js",
    "public\assets\js\smartvolt-dashboard.js",
    "public\assets\js\smartvolt-energy-history.js",
    "public\assets\js\smartvolt-rooms.js",
    "public\assets\js\smartvolt-settings.js",
    "public\assets\js\smartvolt-devices.js"
)

New-Item -ItemType Directory -Path $BackupRoot -Force | Out-Null

foreach ($Relative in $Files) {
    $Source = Join-Path $PatchRoot $Relative
    if (-not (Test-Path $Source)) {
        throw "File paket tidak ditemukan: $Relative"
    }

    $Destination = Join-Path $ProjectRoot $Relative
    $DestinationDirectory = Split-Path -Parent $Destination
    New-Item -ItemType Directory -Path $DestinationDirectory -Force | Out-Null

    if (Test-Path $Destination) {
        $BackupDestination = Join-Path $BackupRoot $Relative
        New-Item -ItemType Directory -Path (Split-Path -Parent $BackupDestination) -Force | Out-Null
        Copy-Item $Destination $BackupDestination -Force
    }

    Copy-Item $Source $Destination -Force
}

Push-Location $ProjectRoot
try {
    php artisan optimize:clear
} finally {
    Pop-Location
}

Write-Host ""
Write-Host "SmartVolt UI V4.1 berhasil dipasang." -ForegroundColor Green
Write-Host "Backup file lama: $BackupRoot"
Write-Host "Jalankan: php artisan serve --host=0.0.0.0 --port=8000"
Write-Host "Buka: http://127.0.0.1:8000"
Write-Host "Tekan Ctrl + F5 pada browser."
