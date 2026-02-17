#!/bin/bash
# SarhIndex Documentation Watcher
# يراقب التغييرات في الكود ويحدث التوثيق تلقائياً

PROJECT_DIR="/home/sarh/سطح المكتب/work/proj/sarh"
LOG_FILE="$PROJECT_DIR/storage/logs/doc-watcher.log"

echo "🔍 بدء مراقبة الكود لتحديث التوثيق تلقائياً..."
echo "📁 المسار: $PROJECT_DIR"
echo "📝 السجل: $LOG_FILE"
echo ""

# التحقق من وجود inotify-tools
if ! command -v inotifywait &> /dev/null; then
    echo "❌ خطأ: inotifywait غير مثبت"
    echo "قم بتثبيته عبر: sudo apt-get install inotify-tools"
    exit 1
fi

# إنشاء ملف السجل إذا لم يكن موجوداً
touch "$LOG_FILE"

# دالة للتحديث
update_docs() {
    echo "$(date '+%Y-%m-%d %H:%M:%S') - 🔄 تحديث التوثيق..." | tee -a "$LOG_FILE"
    cd "$PROJECT_DIR"
    php artisan sarh:auto-document 2>&1 | tee -a "$LOG_FILE"
    echo "$(date '+%Y-%m-%d %H:%M:%S') - ✅ تم التحديث بنجاح" | tee -a "$LOG_FILE"
    echo "" | tee -a "$LOG_FILE"
}

# تحديث أولي
update_docs

# المجلدات التي سيتم مراقبتها
WATCH_DIRS=(
    "$PROJECT_DIR/app/Models"
    "$PROJECT_DIR/app/Http/Controllers"
    "$PROJECT_DIR/app/Services"
    "$PROJECT_DIR/app/Filament/Resources"
    "$PROJECT_DIR/app/Filament/Widgets"
    "$PROJECT_DIR/app/Filament/Pages"
    "$PROJECT_DIR/app/Providers"
    "$PROJECT_DIR/database/migrations"
    "$PROJECT_DIR/routes"
    "$PROJECT_DIR/config"
)

echo "👀 بدء المراقبة..."
echo "اضغط Ctrl+C للإيقاف"
echo ""

# مراقبة التغييرات
while true; do
    inotifywait -r -e modify,create,delete,move \
        "${WATCH_DIRS[@]}" \
        --exclude '(.*\.swp|.*~|\.git)' 2>/dev/null | while read -r directory events filename; do
        
        # تجاهل ملفات معينة
        if [[ "$filename" =~ \.(log|cache)$ ]]; then
            continue
        fi
        
        echo "$(date '+%Y-%m-%d %H:%M:%S') - 📝 تغيير في: $directory$filename" | tee -a "$LOG_FILE"
        
        # الانتظار قليلاً للسماح بحفظ التغييرات
        sleep 2
        
        # تحديث التوثيق
        update_docs
    done
done
