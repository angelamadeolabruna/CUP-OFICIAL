#!/bin/bash
cd /var/www/html/PROTOTIPO-CUP-2
php artisan migrate --force
php artisan db:seed --force
apache2-foreground
