FROM php:8.2-apache

# Cài Composer và module rewrite
RUN apt-get update && apt-get install -y unzip git && \
    curl -sS https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer && \
    a2enmod rewrite

# Copy mã nguồn
COPY . /var/www/html/

# Cài thư viện PHP (JWT, PDO,...)
WORKDIR /var/www/html/
RUN composer install

# Mở port
EXPOSE 80
