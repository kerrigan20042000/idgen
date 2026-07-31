FROM php:8.2-apache

# Install GD extension for image generation (JPEG/PNG processing & TTF fonts)
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

# Copy application files to Apache root
COPY . /var/www/html/

# Enable Apache Mod_Rewrite (if using custom URLs)
RUN a2enmod rewrite

# Set environment port binding for Render (default 10000 or 80)
EXPOSE 80
