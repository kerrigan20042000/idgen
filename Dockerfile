# 1. Use an official PHP image with Apache web server built-in
FROM php:8.2-apache

# 2. Install PostgreSQL system development libraries and compile BOTH extensions
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install mysqli pdo pdo_pgsql \
    && docker-php-ext-enable mysqli pdo_pgsql

# 3. Copy your receiver.php and other files into the web server directory
COPY . /var/www/html/

# 4. Expose standard web traffic port
EXPOSE 80
