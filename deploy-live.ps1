# Full live deploy: build + Square .env sync + FTP upload
# Run: powershell -ExecutionPolicy Bypass -File deploy-live.ps1

$ErrorActionPreference = "Stop"
Set-Location $PSScriptRoot

Write-Host "`n=== Dainely live deploy ===" -ForegroundColor Cyan

Write-Host "`n[1/4] npm run build" -ForegroundColor Yellow
npm run build
if ($LASTEXITCODE -ne 0) { exit 1 }

$manifest = Get-Content "public\build\manifest.json" -Raw | ConvertFrom-Json
$css = $manifest.'resources/css/app.css'.file
$js = $manifest.'resources/js/app.js'.file
Write-Host "  CSS: public/build/$css" -ForegroundColor Green
Write-Host "  JS:  public/build/$js" -ForegroundColor Green

if (-not $env:FTP_PASSWORD) {
    Write-Host "`n[2/4] SKIP env sync — set FTP_PASSWORD first" -ForegroundColor Yellow
    Write-Host "  `$env:FTP_PASSWORD = 'your-password'" -ForegroundColor DarkGray
    Write-Host "`n[3/4] SKIP FTP upload" -ForegroundColor Yellow
    Write-Host "`n[4/4] Manual steps:" -ForegroundColor Cyan
    Write-Host "  1. python scripts/push_server_env.py   (Square keys to server .env)"
    Write-Host "  2. python scripts/ftp_upload.py        (code + build)"
    Write-Host "  3. Server: php artisan config:clear && php artisan view:clear"
    Write-Host "`nOr run: powershell -File deploy-build.ps1  then upload live-build-upload.zip"
    exit 0
}

Write-Host "`n[2/4] Sync Square + tax keys to server .env" -ForegroundColor Yellow
python scripts/push_server_env.py
if ($LASTEXITCODE -ne 0) { exit 1 }

Write-Host "`n[3/4] FTP upload (build + PHP + blade + JS)" -ForegroundColor Yellow
python scripts/ftp_upload.py
if ($LASTEXITCODE -ne 0) { exit 1 }

Write-Host "`n[4/4] Done" -ForegroundColor Green
Write-Host "  Verify: https://dev.dainelylab.com/build/$js"
Write-Host "  Checkout: hard refresh Ctrl+Shift+R"
Write-Host "  Square error gone if server .env synced + config cache cleared`n"
