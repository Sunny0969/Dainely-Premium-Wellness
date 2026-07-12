# Dainely — production build + FTP-ready package
# Run: powershell -ExecutionPolicy Bypass -File deploy-build.ps1

$ErrorActionPreference = "Stop"
Set-Location $PSScriptRoot

Write-Host "`n=== 1/5 NPM build (CSS + JS) ===" -ForegroundColor Cyan
# npm writes warnings to stderr; do not treat as terminating errors
$prevEap = $ErrorActionPreference
$ErrorActionPreference = "Continue"
npm ci
if ($LASTEXITCODE -ne 0) { npm install }
npm run build
$buildExit = $LASTEXITCODE
$ErrorActionPreference = $prevEap
if ($buildExit -ne 0) {
    Write-Host "ERROR: npm run build failed (exit $buildExit)" -ForegroundColor Red
    exit $buildExit
}

Write-Host "`n=== 2/5 Remove Vite dev marker ===" -ForegroundColor Cyan
$hot = Join-Path $PSScriptRoot "public\hot"
if (Test-Path $hot) {
    Remove-Item $hot -Force
    Write-Host "Deleted public/hot" -ForegroundColor Yellow
}

Write-Host "`n=== 3/5 Verify build output ===" -ForegroundColor Cyan
$manifest = Join-Path $PSScriptRoot "public\build\manifest.json"
if (-not (Test-Path $manifest)) {
    Write-Host "ERROR: public/build/manifest.json missing!" -ForegroundColor Red
    exit 1
}
$buildFiles = Get-ChildItem (Join-Path $PSScriptRoot "public\build") -Recurse -File
foreach ($f in $buildFiles) {
    Write-Host "  OK $($f.FullName.Replace($PSScriptRoot + '\', ''))"
}

Write-Host "`n=== 4/5 Create FTP upload package ===" -ForegroundColor Cyan
$outRoot = Join-Path $PSScriptRoot "deploy-output"
if (Test-Path $outRoot) { Remove-Item $outRoot -Recurse -Force }
New-Item -ItemType Directory -Path (Join-Path $outRoot "public\build") -Force | Out-Null
New-Item -ItemType Directory -Path (Join-Path $outRoot "dev.dainelylab.com\build") -Force | Out-Null

Copy-Item -Path "public\build\*" -Destination (Join-Path $outRoot "public\build") -Recurse -Force
Copy-Item -Path "public\build\*" -Destination (Join-Path $outRoot "dev.dainelylab.com\build") -Recurse -Force

$zipPath = Join-Path $PSScriptRoot "live-build-upload.zip"
if (Test-Path $zipPath) { Remove-Item $zipPath -Force }
Compress-Archive -Path (Join-Path $outRoot "*") -DestinationPath $zipPath -Force

Write-Host "`n=== 5/5 Upload instructions ===" -ForegroundColor Cyan
$manifestJson = Get-Content $manifest -Raw | ConvertFrom-Json
$cssFile = $manifestJson.'resources/css/app.css'.file
$jsFile = $manifestJson.'resources/js/app.js'.file
Write-Host "  CSS: build/$cssFile" -ForegroundColor Green
Write-Host "  JS:  build/$jsFile" -ForegroundColor Green
Write-Host "`n=== BUILD DONE ===" -ForegroundColor Green
Write-Host "Upload folder: deploy-output\"
Write-Host "Or zip:        live-build-upload.zip"
Write-Host "See DEPLOY-NOW.txt`n"
