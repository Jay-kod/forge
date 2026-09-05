# PowerShell Production Database & Storage Backup Script for FORGE
$ErrorActionPreference = "Stop"

$Timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
$BackupDir = "storage\backups"
$RetentionDays = 7

Write-Host "=== [FORGE Backup Automation] Starting Point-in-Time Backup ($Timestamp) ===" -ForegroundColor Cyan

if (-not (Test-Path $BackupDir)) {
    New-Item -ItemType Directory -Path $BackupDir -Force | Out-Null
}

# 1. Database Backup
$DbName = if ($env:DB_DATABASE) { $env:DB_DATABASE } else { "forge" }
$DbHost = if ($env:DB_HOST) { $env:DB_HOST } else { "127.0.0.1" }
$DbPort = if ($env:DB_PORT) { $env:DB_PORT } else { "3306" }
$DbUser = if ($env:DB_USERNAME) { $env:DB_USERNAME } else { "root" }
$DbPass = $env:DB_PASSWORD

$DbBackupFile = Join-Path $BackupDir "forge_db_${Timestamp}.sql"

Write-Host "--> Exporting MySQL database [$DbName]..." -ForegroundColor Yellow
$mysqldump = Get-Command "mysqldump" -ErrorAction SilentlyContinue
if ($mysqldump) {
    & mysqldump --host=$DbHost --port=$DbPort --user=$DbUser --password=$DbPass $DbName > $DbBackupFile
    Write-Host "✓ Database backup generated: $DbBackupFile" -ForegroundColor Green
} else {
    Write-Host "⚠ mysqldump executable not found in PATH." -ForegroundColor Yellow
}

# 2. Storage & Uploads Archive
$StorageBackupFile = Join-Path $BackupDir "forge_storage_${Timestamp}.zip"
Write-Host "--> Archiving storage\app..." -ForegroundColor Yellow
if (Test-Path "storage\app") {
    Compress-Archive -Path "storage\app" -DestinationPath $StorageBackupFile -Force
    Write-Host "✓ Storage archive generated: $StorageBackupFile" -ForegroundColor Green
}

# 3. Retention policy cleanup
Write-Host "--> Pruning backups older than $RetentionDays days..." -ForegroundColor Yellow
$CutoffDate = (Get-Date).AddDays(-$RetentionDays)
Get-ChildItem -Path $BackupDir -Filter "forge_*" | Where-Object { $_.LastWriteTime -lt $CutoffDate } | Remove-Item -Force

Write-Host "=== [FORGE Backup Automation] Backup Process Completed! ===" -ForegroundColor Green
