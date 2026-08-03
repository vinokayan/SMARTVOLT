param(
    [string]$ProjectRoot = "C:\SMARTVOLT"
)

$ErrorActionPreference = "Stop"
$SourceRoot = Split-Path -Parent $MyInvocation.MyCommand.Path

if (-not (Test-Path (Join-Path $ProjectRoot "artisan"))) {
    throw "Folder proyek Laravel tidak ditemukan: $ProjectRoot"
}

$timestamp = Get-Date -Format "yyyyMMdd-HHmmss"
$backupRoot = Join-Path $ProjectRoot "storage\ui-backups\modern-v2-$timestamp"
$roots = @("app", "routes", "resources", "public")

foreach ($root in $roots) {
    $sourcePath = Join-Path $SourceRoot $root
    if (-not (Test-Path $sourcePath)) {
        continue
    }

    Get-ChildItem $sourcePath -Recurse -File | ForEach-Object {
        $relative = $_.FullName.Substring($SourceRoot.Length).TrimStart('\', '/')
        $target = Join-Path $ProjectRoot $relative

        if (Test-Path $target) {
            $backup = Join-Path $backupRoot $relative
            New-Item -ItemType Directory -Force -Path (Split-Path $backup -Parent) | Out-Null
            Copy-Item $target $backup -Force
        }

        New-Item -ItemType Directory -Force -Path (Split-Path $target -Parent) | Out-Null
        Copy-Item $_.FullName $target -Force
        Write-Host "Diperbarui: $relative"
    }
}

Push-Location $ProjectRoot
try {
    if (Get-Command php -ErrorAction SilentlyContinue) {
        php artisan optimize:clear
    } else {
        Write-Warning "PHP tidak ditemukan di PATH. Jalankan 'php artisan optimize:clear' secara manual."
    }
} finally {
    Pop-Location
}

Write-Host ""
Write-Host "SmartVolt Modern Responsive V2 berhasil dipasang."
Write-Host "Backup file lama: $backupRoot"
