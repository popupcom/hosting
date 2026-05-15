#!/bin/bash
set -e

cd ~/hosting || exit

PHP=/usr/bin/php84
COMPOSER=/usr/bin/composer

echo "Pull latest changes..."
git pull origin main

echo "Install composer packages..."
$PHP $COMPOSER install --no-dev --optimize-autoloader

if command -v npm >/dev/null 2>&1; then
  echo "Build frontend assets..."
  npm ci --ignore-scripts
  npm run build
fi

echo "Run migrations..."
$PHP artisan migrate --force

echo "Clear caches..."
$PHP artisan optimize:clear

echo "Rebuild caches..."
$PHP artisan optimize

echo "Restart queue workers..."
$PHP artisan queue:restart

echo "Done."
