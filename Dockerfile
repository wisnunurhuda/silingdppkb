FROM php:8.2-apache

# Instal ekstensi mysqli, pdo, dan pdo_mysql secara eksplisit
RUN docker-php-ext-install mysqli pdo pdo_mysql \
    && docker-php-ext-enable mysqli pdo pdo_mysql

# Salin seluruh file proyek ke root web server Apache
COPY . /var/www/html/

# Berikan hak akses penuh ke folder web
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html