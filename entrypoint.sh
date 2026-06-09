#!/bin/bash
cd /var/www/html/PROTOTIPO-CUP-2
php artisan migrate --force
chown -R www-data:www-data storage/ bootstrap/cache/
apache2-foreground
