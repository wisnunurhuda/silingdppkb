FROM php:8.2-apache

# Mengaktifkan ekstensi database mysqli dan pdo_mysql
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Menyalin seluruh file proyek ke folder web server
COPY . /var/www/html/

# Mengatur port Apache agar selaras dengan Railway
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# Memberikan hak akses penuh
RUN chown -R www-data:www-data /var/www/html