FROM php:8.2-apache

# Instal ekstensi mysqli, pdo, dan pdo_mysql
RUN docker-php-ext-install mysqli pdo pdo_mysql \
    && docker-php-ext-enable mysqli pdo pdo_mysql

# Salin seluruh file proyek ke root web server Apache
COPY . /var/www/html/

# Konfigurasi Apache agar mendengarkan port dinamis dari Railway
RUN sed -i 's/80/80/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf
RUN echo "Listen \${PORT:-80}" > /etc/apache2/ports.conf
RUN sed -i 's/:80/:${PORT:-80}/g' /etc/apache2/sites-available/000-default.conf

# Berikan hak akses penuh ke folder web
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html