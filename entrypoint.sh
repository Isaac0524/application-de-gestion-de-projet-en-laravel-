#!/bin/bash
# Attendre MySQL avant migrations
echo "⏳ Attente de la base MySQL..."
until nc -z -v -w30 mysql 3306
do
  echo "❌ MySQL indisponible - attente..."
  sleep 5
done

# Fix permissions before running Laravel commands
echo "🔧 Configuration des permissions..."
chown -R www-data:www-data /var/www/storage
chown -R www-data:www-data /var/www/bootstrap/cache
chmod -R 775 /var/www/storage
chmod -R 775 /var/www/bootstrap/cache

echo "✅ MySQL dispo ! Lancement migrations..."
php artisan migrate --seed --force

exec "$@"
