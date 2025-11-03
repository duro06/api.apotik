#!/bin/sh

APP_DIR="/var/www"

echo "⏳ Menunggu folder ${APP_DIR} tersedia..."
while [ ! -d "$APP_DIR" ]; do
  sleep 1
done

echo "✅ Folder ditemukan, menjalankan Nginx..."
nginx -g "daemon off;"
