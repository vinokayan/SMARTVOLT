param(
    [string]$ProjectRoot = "C:\SMARTVOLT"
)

$ErrorActionPreference = "Stop"
$PackageRoot = Split-Path -Parent $MyInvocation.MyCommand.Path
$PayloadRoot = Join-Path $PackageRoot "payload"
$Timestamp = Get-Date -Format "yyyyMMdd-HHmmss"
$BackupRoot = Join-Path $ProjectRoot "storage\ui-backups\google-login-$Timestamp"

function Ensure-Line {
    param(
        [string]$Path,
        [string]$Key,
        [string]$DefaultLine
    )

    $content = Get-Content -Raw -Path $Path
    if ($content -notmatch "(?m)^$([regex]::Escape($Key))=") {
        $updated = $content.TrimEnd() + "`r`n$DefaultLine`r`n"
        [System.IO.File]::WriteAllText(
            $Path,
            $updated,
            (New-Object System.Text.UTF8Encoding($false))
        )
    }
}

if (-not (Test-Path (Join-Path $ProjectRoot "artisan"))) {
    throw "Folder Laravel tidak ditemukan: $ProjectRoot"
}

if (-not (Get-Command composer -ErrorAction SilentlyContinue)) {
    throw "Composer tidak ditemukan. Instal Composer atau jalankan perintah melalui terminal yang mengenali composer."
}

New-Item -ItemType Directory -Path $BackupRoot -Force | Out-Null

$FilesToBackup = @(
    "app\Models\User.php",
    "routes\web.php",
    "resources\views\auth\login.blade.php",
    "public\assets\css\smartvolt-auth.css",
    "config\services.php",
    ".env"
)

foreach ($relative in $FilesToBackup) {
    $source = Join-Path $ProjectRoot $relative
    if (Test-Path $source) {
        $destination = Join-Path $BackupRoot $relative
        New-Item -ItemType Directory -Path (Split-Path -Parent $destination) -Force | Out-Null
        Copy-Item $source $destination -Force
    }
}

Write-Host "Memasang Laravel Socialite..." -ForegroundColor Cyan
Push-Location $ProjectRoot
composer require laravel/socialite --no-interaction
Pop-Location

Write-Host "Menyalin file Google Login..." -ForegroundColor Cyan
Copy-Item (Join-Path $PayloadRoot "*") $ProjectRoot -Recurse -Force

$servicesPath = Join-Path $ProjectRoot "config\services.php"
$services = Get-Content -Raw -Path $servicesPath
if ($services -notmatch "(?m)^\s*'google'\s*=>\s*\[") {
    $googleBlock = @"

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],
"@
    $lastClose = $services.LastIndexOf('];')
    if ($lastClose -lt 0) {
        throw "Struktur config/services.php tidak dikenali."
    }
    $services = $services.Insert($lastClose, $googleBlock)
    [System.IO.File]::WriteAllText(
        $servicesPath,
        $services,
        (New-Object System.Text.UTF8Encoding($false))
    )
}

$envPath = Join-Path $ProjectRoot ".env"
Ensure-Line -Path $envPath -Key "GOOGLE_CLIENT_ID" -DefaultLine "GOOGLE_CLIENT_ID="
Ensure-Line -Path $envPath -Key "GOOGLE_CLIENT_SECRET" -DefaultLine "GOOGLE_CLIENT_SECRET="
Ensure-Line -Path $envPath -Key "GOOGLE_REDIRECT_URI" -DefaultLine "GOOGLE_REDIRECT_URI=http://127.0.0.1:8000/auth/google/callback"

Write-Host "Menjalankan migration..." -ForegroundColor Cyan
Push-Location $ProjectRoot
php artisan migrate --force
php artisan optimize:clear
Pop-Location

Write-Host "" 
Write-Host "Google Login berhasil dipasang." -ForegroundColor Green
Write-Host "Backup: $BackupRoot" -ForegroundColor DarkGray
Write-Host "" 
Write-Host "Langkah wajib berikutnya:" -ForegroundColor Yellow
Write-Host "1. Buat OAuth Client ID di Google Cloud Console."
Write-Host "2. Daftarkan redirect URI: http://127.0.0.1:8000/auth/google/callback"
Write-Host "3. Isi GOOGLE_CLIENT_ID dan GOOGLE_CLIENT_SECRET pada .env"
Write-Host "4. Jalankan: php artisan optimize:clear"
