FROM php:8.2-apache

# Instal ekstensi database mysqli dan pdo_mysql
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Salin seluruh file aplikasi ke direktori web Apache
COPY . /var/www/html/

# Sesuaikan port Apache agar membaca port dinamis dari environment Railway
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# Berikan izin akses penuh untuk web server
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Menjalankan Apache secara foreground agar container tidak crash/tertutup
CMD ["apache2-foreground"]