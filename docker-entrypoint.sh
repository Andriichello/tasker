#!/bin/bash

# Create .env
echo "Creating .env from .env.docker"
cp .env.docker .env

# Wait for the database to be ready
echo "Waiting for database connection..."
while ! nc -z db 3306; do
  sleep 1
done
echo "Database is ready!"

# Install dependencies
echo "Installing dependencies..."
composer install --no-interaction --optimize-autoloader

# Generate application key if not set
if [ -z "$(grep -E '^APP_KEY=[a-zA-Z0-9:+\/=]+' .env)" ]; then
  echo "Generating application key..."
  php artisan key:generate
fi

echo "Generating OpenAPI specification..."
php artisan l5-swagger:generate

# Run migrations
echo "Running migrations..."
php artisan migrate --force --seed

# Clear caches
echo "Clearing caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Set permissions
echo "Setting permissions..."
chmod -R 775 storage bootstrap/cache

# Start PHP-FPM
echo "Starting PHP-FPM..."
php-fpm
