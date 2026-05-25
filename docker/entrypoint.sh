#!/bin/sh
set -e

echo "🚀 Starting PodWave application..."

# Generate app key if not exists
if [ -z "$APP_KEY" ]; then
    echo "📝 Generating APP_KEY..."
    php artisan key:generate --force
fi

# Run database migrations
echo "🔄 Running database migrations..."
php artisan migrate --force

# Clear and cache configuration
echo "🔧 Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start supervisor to manage PHP-FPM and Nginx
echo "✅ Starting services..."
exec /usr/bin/supervisord -c /etc/supervisord.conf
