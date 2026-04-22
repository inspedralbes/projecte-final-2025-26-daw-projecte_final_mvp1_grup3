# Reconstrueix la imatge del frontend quan Docker Desktop falla amb
# "parent snapshot ... does not exist" (BuildKit / capes corruptes).
$ErrorActionPreference = "Stop"
Set-Location $PSScriptRoot

Write-Host "Netejant memòria cau del builder..." -ForegroundColor Cyan
docker builder prune -af

Write-Host "Construint frontend sense memòria cau..." -ForegroundColor Cyan
docker compose build --no-cache frontend

Write-Host "Arrencant serveis..." -ForegroundColor Cyan
docker compose up -d

Write-Host "Fet." -ForegroundColor Green
