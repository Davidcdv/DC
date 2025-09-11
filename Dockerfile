FROM php:8.2-apache

# Enable useful Apache modules
RUN a2enmod rewrite headers expires

# Install system deps (optional) and PHP extensions as needed
RUN docker-php-ext-install mysqli || true

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy app source
COPY . /var/www/html

# Install PHP dependencies if composer.json exists
RUN if [ -f composer.json ]; then composer install --no-dev --prefer-dist --no-interaction; fi

# Expose HTTP
EXPOSE 80

# Default Apache startup handled by base image

