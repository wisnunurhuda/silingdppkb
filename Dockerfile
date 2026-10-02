FROM php:8.2-apache

# Instal ekstensi database MySQL untuk PHP
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Salin seluruh file proyek ke direktori web server Apache
COPY . /var/www/html/

# Berikan izin akses folder
RUN chown -R www-data:www-data /var/www/html