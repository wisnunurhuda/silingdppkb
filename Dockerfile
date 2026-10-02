FROM php:8.2-apache

# Instal ekstensi database mysqli dan pdo_mysql
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Salin seluruh file aplikasi ke direktori web Apache
COPY . /var/www/html/

# Berikan izin akses penuh untuk web server
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Apache akan otomatis berjalan di port 80 secara stabil
EXPOSE 80
CMD ["apache2-foreground"]