FROM php:8.2-apache

# Instalar dependencias
RUN apt-get update && apt-get install -y \
    libpq-dev zip unzip git curl \
    && docker-php-ext-install pdo pdo_pgsql \
    && docker-php-ext-enable opcache

# Configuración de opcache
RUN { \
    echo 'opcache.memory_consumption=128'; \
    echo 'opcache.interned_strings_buffer=8'; \
    echo 'opcache.max_accelerated_files=10000'; \
    echo 'opcache.revalidate_freq=2'; \
    echo 'opcache.fast_shutdown=1'; \
    echo 'opcache.enable_cli=1'; \
} > /usr/local/etc/php/conf.d/opcache.ini

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copiar proyecto (la app está en PROTOTIPO-CUP-2)
WORKDIR /var/www/html
COPY . .

# Instalar dependencias de Laravel
WORKDIR /var/www/html/PROTOTIPO-CUP-2
RUN composer install --no-dev --optimize-autoloader

# Permisos
RUN chown -R www-data:www-data storage bootstrap/cache

# Configurar Apache para apuntar a PROTOTIPO-CUP-2/public
ENV APACHE_DOCUMENT_ROOT /var/www/html/PROTOTIPO-CUP-2/public
RUN sed -i 's|/var/www/html|/var/www/html/PROTOTIPO-CUP-2/public|g' /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite
RUN a2enmod expires

# Entrypoint para ejecutar migraciones al iniciar
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80
CMD ["/entrypoint.sh"]
