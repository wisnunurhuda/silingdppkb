FROM php:8.2-apache

# Perbaiki error modul MPM Apache yang bentrok
RUN rm -f /etc/apache2/mods-enabled/mpm_event.load \
    && rm -f /etc/apache2/mods-enabled/mpm_event.conf \
    && a2enmod mpm_prefork

# Instal ekstensi mysqli dan pdo_mysql untuk database
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Salin seluruh file aplikasi ke direktori web Apache
COPY . /var/www/html/

# Izinkan akses penuh untuk web server
RUN chown -R www-data:www-data /var/www/html