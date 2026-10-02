FROM php:8.2-apache

# Nonaktifkan event MPM dan aktifkan prefork MPM dengan aman
RUN a2dismod mpm_event || true \
    && a2dismod mpm_worker || true \
    && a2enmod mpm_prefork

# Instal ekstensi database mysqli dan pdo_mysql
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Salin seluruh file aplikasi ke direktori web Apache
COPY . /var/www/html/

# Berikan izin akses penuh untuk web server
RUN chown -R www-data:www-data /var/www/html