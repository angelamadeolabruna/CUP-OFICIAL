#!/bin/bash
cd /var/www/html/PROTOTIPO-CUP-2

php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

chown -R www-data:www-data storage/ bootstrap/cache/
apache2-foreground
