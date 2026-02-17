#!/bin/bash

# ================================
# نشر سريع على sarh.online
# ================================

set -e

SERVER="u850419603@145.223.119.139"
PORT="65002"
PROJECT_PATH="/home/u850419603/sarh"

echo "🚀 بدء النشر..."

# 1. Commit وPush محلياً
echo ""
echo "📦 Commit & Push..."
git add -A
git commit -m "deploy: Quick deployment $(date +%Y-%m-%d_%H:%M:%S)" || echo "لا توجد تغييرات للـ commit"
git push origin main

# 2. تحديث السيرفر
echo ""
echo "🌐 تحديث السيرفر..."
DOMAIN_PUBLIC="/home/u850419603/domains/sarh.online/public_html"
ssh -p $PORT $SERVER "cd $PROJECT_PATH && \
    git fetch origin main && \
    git reset --hard origin/main && \
    php artisan migrate --force && \
    php artisan optimize:clear && \
    php artisan optimize && \
    echo '📁 Syncing public assets to domain public_html...' && \
    cp -r $PROJECT_PATH/public/build $DOMAIN_PUBLIC/ && \
    cp -r $PROJECT_PATH/public/css $DOMAIN_PUBLIC/ 2>/dev/null; \
    cp -r $PROJECT_PATH/public/js $DOMAIN_PUBLIC/ 2>/dev/null; \
    cp $PROJECT_PATH/public/.htaccess $DOMAIN_PUBLIC/ 2>/dev/null; \
    echo '✅ Assets synced to public_html'"

echo ""
echo "✅ النشر مكتمل!"
echo "   🔗 https://sarh.online"
