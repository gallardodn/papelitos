#!/usr/bin/env sh
set -e

if [ -n "${APP_DATA_DIR:-}" ]; then
  DATA_DIR="$APP_DATA_DIR"
else
  DATA_DIR="/var/www/data"
fi

mkdir -p "$DATA_DIR" "$DATA_DIR/uploads" "$DATA_DIR/drawings"

chown -R www-data:www-data "$DATA_DIR" 2>/dev/null || true

if [ -f "/var/www/html/composer.json" ] && [ ! -f "/var/www/html/vendor/autoload.php" ]; then
  composer install --no-interaction --no-progress --no-dev --prefer-dist
fi

exec "$@"
