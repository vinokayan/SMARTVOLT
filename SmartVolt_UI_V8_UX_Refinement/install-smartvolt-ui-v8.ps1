param(
    [string]$ProjectRoot = "C:\SMARTVOLT"
)

$ErrorActionPreference = "Stop"
$ScriptRoot = Split-Path -Parent $MyInvocation.MyCommand.Path
$PayloadRoot = Join-Path $ScriptRoot "payload"

Write-Host ""
Write-Host "SmartVolt UI V8 - UX Refinement" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan

if (-not (Test-Path $PayloadRoot)) {
    throw "Folder payload tidak ditemukan: $PayloadRoot"
}

if (-not (Test-Path $ProjectRoot)) {
    throw "Folder proyek tidak ditemukan: $ProjectRoot"
}

$ArtisanPath = Join-Path $ProjectRoot "artisan"
if (-not (Test-Path $ArtisanPath)) {
    throw "Folder tujuan bukan proyek Laravel SmartVolt karena file artisan tidak ditemukan."
}

$Timestamp = Get-Date -Format "yyyyMMdd-HHmmss"
$BackupRoot = Join-Path $ProjectRoot "storage\ui-backups\v8-ux-refinement-$Timestamp"
New-Item -ItemType Directory -Path $BackupRoot -Force | Out-Null

$Files = Get-ChildItem -Path $PayloadRoot -File -Recurse

foreach ($File in $Files) {
    $RelativePath = $File.FullName.Substring($PayloadRoot.Length).TrimStart('\', '/')
    $TargetPath = Join-Path $ProjectRoot $RelativePath

    if (Test-Path $TargetPath) {
        $BackupPath = Join-Path $BackupRoot $RelativePath
        $BackupDirectory = Split-Path -Parent $BackupPath
        New-Item -ItemType Directory -Path $BackupDirectory -Force | Out-Null
        Copy-Item -Path $TargetPath -Destination $BackupPath -Force
    }

    $TargetDirectory = Split-Path -Parent $TargetPath
    New-Item -ItemType Directory -Path $TargetDirectory -Force | Out-Null
    Copy-Item -Path $File.FullName -Destination $TargetPath -Force

    Write-Host "[OK] $RelativePath" -ForegroundColor Green
}

Write-Host ""
Write-Host "File lama dicadangkan ke:" -ForegroundColor Yellow
Write-Host $BackupRoot

$PhpCommand = Get-Command php -ErrorAction SilentlyContinue
if ($PhpCommand) {
    Push-Location $ProjectRoot
    try {
        php artisan optimize:clear
    }
    finally {
        Pop-Location
    }
} else {
    Write-Host "PHP tidak ditemukan di PATH. Jalankan 'php artisan optimize:clear' secara manual." -ForegroundColor Yellow
}

Write-Host ""
Write-Host "Pemasangan SmartVolt UI V8 berhasil." -ForegroundColor Green
Write-Host "Buka website lalu tekan Ctrl + F5." -ForegroundColor Cyan
