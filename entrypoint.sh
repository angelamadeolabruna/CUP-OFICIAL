#!/bin/bash
cd /var/www/html/PROTOTIPO-CUP-2
php artisan migrate --force
apache2-foreground
