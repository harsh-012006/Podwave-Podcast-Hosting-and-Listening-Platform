#!/bin/bash
# ============================================================
#  PodWave — Quick Setup Script
#  Run this after cloning the repository.
# ============================================================

set -e

echo "🎙️  PodWave Setup Script"
echo "========================"

# 1. Copy .env
if [ ! -f .env ]; then
    cp .env.example .env
    echo "✅ .env created from .env.example"
else
    echo "⚠️  .env already exists — skipping copy"
fi

# 2. Install PHP dependencies
echo ""
echo "📦 Installing Composer dependencies..."
composer install --no-interaction --prefer-dist --optimize-autoloader

# 3. Generate app key
echo ""
echo "🔑 Generating application key..."
php artisan key:generate

# 4. Install Node dependencies
echo ""
echo "📦 Installing NPM dependencies..."
npm install

# 5. Build frontend assets
echo ""
echo "🔨 Building frontend assets..."
npm run build

# 6. Run migrations and seed
echo ""
echo "🗄️  Running database migrations and seeding..."
php artisan migrate --seed --force

# 7. Create storage symlink
echo ""
echo "🔗 Creating storage symlink..."
php artisan storage:link

# 8. Clear and optimize caches
echo ""
echo "⚡ Optimizing..."
php artisan config:clear
php artisan view:clear
php artisan route:clear

echo ""
echo "✅ PodWave setup complete!"
echo ""
echo "🌐 Run the app:"
echo "   php artisan serve"
echo ""
echo "🔐 Demo credentials:"
echo "   Admin:    admin@podwave.fm   / password"
echo "   Creator:  creator@podwave.fm / password"
echo "   Listener: listener@podwave.fm / password"
echo ""
echo "Visit: http://localhost:8000"
