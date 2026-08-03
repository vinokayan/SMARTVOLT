param(
    [string]$ProjectRoot = "C:\SMARTVOLT"
)

$ErrorActionPreference = "Stop"
$PatchRoot = Split-Path -Parent $MyInvocation.MyCommand.Path
$Timestamp = Get-Date -Format "yyyyMMdd-HHmmss"
$BackupRoot = Join-Path $ProjectRoot "storage\ui-backups\v3-$Timestamp"

if (-not (Test-Path (Join-Path $ProjectRoot "artisan"))) {
    throw "Folder Laravel tidak ditemukan: $ProjectRoot"
}

$Targets = @(
    "resources\views",
    "public\assets\css\smartvolt-v3.css",
    "public\assets\css\smartvolt-auth-v3.css",
    "public\assets\js"
)

New-Item -ItemType Directory -Path $BackupRoot -Force | Out-Null

foreach ($Relative in $Targets) {
    $Source = Join-Path $PatchRoot $Relative
    if (-not (Test-Path $Source)) { continue }

    $Destination = Join-Path $ProjectRoot $Relative

    if (Test-Path $Destination) {
        $BackupDestination = Join-Path $BackupRoot $Relative
        if ((Get-Item $Destination).PSIsContainer) {
            New-Item -ItemType Directory -Path $BackupDestination -Force | Out-Null
            Copy-Item (Join-Path $Destination "*") $BackupDestination -Recurse -Force
        } else {
            New-Item -ItemType Directory -Path (Split-Path -Parent $BackupDestination) -Force | Out-Null
            Copy-Item $Destination $BackupDestination -Force
        }
    }

    if ((Get-Item $Source).PSIsContainer) {
        New-Item -ItemType Directory -Path $Destination -Force | Out-Null
        Copy-Item (Join-Path $Source "*") $Destination -Recurse -Force
    } else {
        New-Item -ItemType Directory -Path (Split-Path -Parent $Destination) -Force | Out-Null
        Copy-Item $Source $Destination -Force
    }
}

Push-Location $ProjectRoot
try {
    php artisan optimize:clear
} finally {
    Pop-Location
}

Write-Host ""
Write-Host "SmartVolt UI V3 berhasil dipasang." -ForegroundColor Green
Write-Host "Backup: $BackupRoot"
Write-Host "Jalankan: php artisan serve --host=0.0.0.0 --port=8000"
Write-Host "Kemudian tekan Ctrl + F5 pada browser."
