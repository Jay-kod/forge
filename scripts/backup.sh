#!/usr/bin/env bash
set -e

# ==============================================================================
# FORGE Production Database & Storage Backup Script
# ==============================================================================

TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_DIR="${BACKUP_DIR:-storage/backups}"
RETENTION_DAYS=7

echo "=== [FORGE Backup Automation] Starting Point-in-Time Backup (${TIMESTAMP}) ==="

mkdir -p "${BACKUP_DIR}"

# 1. Database Backup (mysqldump -> gzip)
DB_USER="${DB_USERNAME:-forge_user}"
DB_PASS="${DB_PASSWORD:-secret}"
DB_NAME="${DB_DATABASE:-forge_prod}"
DB_HOST="${DB_HOST:-mysql}"
DB_PORT="${DB_PORT:-3306}"

DB_BACKUP_FILE="${BACKUP_DIR}/forge_db_${TIMESTAMP}.sql.gz"

echo "--> Dumping MySQL database [${DB_NAME}] to ${DB_BACKUP_FILE}..."
if command -v mysqldump &> /dev/null; then
    mysqldump --single-transaction --quick --lock-tables=false \
        -h "${DB_HOST}" -P "${DB_PORT}" -u "${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" | gzip > "${DB_BACKUP_FILE}"
    echo "✓ Database backup generated ($(du -h "${DB_BACKUP_FILE}" | cut -f1))"
else
    echo "⚠ mysqldump not found on host. If using Docker, run inside the mysql container or compose exec."
fi

# 2. Storage & Uploads Archive (tar -> gzip)
STORAGE_BACKUP_FILE="${BACKUP_DIR}/forge_storage_${TIMESTAMP}.tar.gz"
echo "--> Archiving storage/app user artifacts to ${STORAGE_BACKUP_FILE}..."
if [ -d "storage/app" ]; then
    tar -czf "${STORAGE_BACKUP_FILE}" -C storage app
    echo "✓ Storage artifacts archive generated ($(du -h "${STORAGE_BACKUP_FILE}" | cut -f1))"
fi

# 3. Clean up backups older than retention policy (default: 7 days)
echo "--> Applying retention policy (purging archives older than ${RETENTION_DAYS} days)..."
find "${BACKUP_DIR}" -type f -name "forge_*" -mtime +"${RETENTION_DAYS}" -exec rm -f {} \;

echo "=== [FORGE Backup Automation] Point-in-Time Backup Completed Successfully! ==="
