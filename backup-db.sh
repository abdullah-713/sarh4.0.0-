#!/bin/bash
###############################################################
# SarhIndex — Daily Database Backup Script
# يتم تشغيله عبر Cron Job في لوحة Hostinger
# الأمر: cd /home/u850419603/sarh && bash backup-db.sh
###############################################################

BACKUP_DIR="/home/u850419603/backups"
PROJECT_DIR="/home/u850419603/sarh"
DATE=$(date +%Y%m%d_%H%M)
KEEP_DAYS=30

# قراءة بيانات الاتصال من .env
DB_DATABASE=$(grep "^DB_DATABASE=" "$PROJECT_DIR/.env" | cut -d'=' -f2)
DB_USERNAME=$(grep "^DB_USERNAME=" "$PROJECT_DIR/.env" | cut -d'=' -f2)
DB_PASSWORD=$(grep "^DB_PASSWORD=" "$PROJECT_DIR/.env" | cut -d'=' -f2 | tr -d '"')

# إنشاء مجلد النسخ الاحتياطي
mkdir -p "$BACKUP_DIR"

# إنشاء النسخة الاحتياطية
BACKUP_FILE="$BACKUP_DIR/sarh_${DATE}.sql.gz"
mysqldump -u "$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" \
    --single-transaction \
    --routines \
    --triggers \
    --quick \
    2>/dev/null | gzip > "$BACKUP_FILE"

if [ $? -eq 0 ] && [ -s "$BACKUP_FILE" ]; then
    SIZE=$(du -h "$BACKUP_FILE" | cut -f1)
    echo "[$(date)] ✓ Backup created: $BACKUP_FILE ($SIZE)"

    # حذف النسخ الأقدم من KEEP_DAYS يوم
    find "$BACKUP_DIR" -name "sarh_*.sql.gz" -mtime +$KEEP_DAYS -delete 2>/dev/null
    REMAINING=$(ls -1 "$BACKUP_DIR"/sarh_*.sql.gz 2>/dev/null | wc -l)
    echo "[$(date)] ✓ Cleanup done. $REMAINING backup(s) retained."
else
    echo "[$(date)] ✗ Backup FAILED!" >&2
    rm -f "$BACKUP_FILE" 2>/dev/null
    exit 1
fi
