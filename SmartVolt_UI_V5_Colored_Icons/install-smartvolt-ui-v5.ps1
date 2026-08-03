param(
    [Parameter(Mandatory = $false)]
    [string]$ProjectRoot = "C:\SMARTVOLT"
)

$ErrorActionPreference = 'Stop'

$ProjectRoot = [System.IO.Path]::GetFullPath($ProjectRoot)
$PayloadRoot = Join-Path $PSScriptRoot 'payload'

if (-not (Test-Path $PayloadRoot)) {
    throw "Folder payload tidak ditemukan: $PayloadRoot"
}

if (-not (Test-Path (Join-Path $ProjectRoot 'artisan'))) {
    throw "Folder tujuan bukan proyek Laravel SmartVolt: $ProjectRoot"
}

$timestamp = Get-Date -Format 'yyyyMMdd-HHmmss'
$backupRoot = Join-Path $ProjectRoot "storage\ui-backups\v5-colored-icons-$timestamp"
New-Item -ItemType Directory -Path $backupRoot -Force | Out-Null

$files = Get-ChildItem $PayloadRoot -Recurse -File

foreach ($file in $files) {
    $relativePath = $file.FullName.Substring($PayloadRoot.Length).TrimStart('\', '/')
    $destination = Join-Path $ProjectRoot $relativePath
    $backup = Join-Path $backupRoot $relativePath

    if (Test-Path $destination) {
        New-Item -ItemType Directory -Path (Split-Path $backup -Parent) -Force | Out-Null
        Copy-Item $destination $backup -Force
    }

    New-Item -ItemType Directory -Path (Split-Path $destination -Parent) -Force | Out-Null
    Copy-Item $file.FullName $destination -Force
    Write-Host "[OK] $relativePath" -ForegroundColor Green
}

Push-Location $ProjectRoot
try {
    if (Get-Command php -ErrorAction SilentlyContinue) {
        php artisan optimize:clear
    } else {
        Write-Warning 'PHP tidak ditemukan di PATH. Jalankan php artisan optimize:clear secara manual.'
    }
}
finally {
    Pop-Location
}

Write-Host ""
Write-Host "SmartVolt UI V5 berhasil dipasang." -ForegroundColor Cyan
Write-Host "Backup file lama: $backupRoot" -ForegroundColor Yellow
Write-Host "Tekan Ctrl + F5 pada browser setelah website dibuka." -ForegroundColor White
