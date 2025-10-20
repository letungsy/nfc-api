# Dùng PHP có sẵn Apache (đỡ phải setup server)
FROM php:8.2-apache

# Cài Composer (nếu cần)
RUN apt-get update && apt-get install -y unzip git && \
    curl -sS https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer

# Copy toàn bộ mã nguồn vào container
COPY . /var/www/html/

# Cài các thư viện PHP cần thiết
WORKDIR /var/www/html/
RUN composer install

# Mở port 80
EXPOSE 80
