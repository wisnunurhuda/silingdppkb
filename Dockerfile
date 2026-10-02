FROM php:8.2-apache

# Instal ekstensi mysqli dan pdo_mysql untuk koneksi database
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Salin seluruh file aplikasi ke direktori web Apache
COPY . /var/www/html/

# Izinkan akses penuh untuk web server
RUN chown -R www-data:www-data /var/www/html