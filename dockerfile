# Laravel SPA Backend Dockerfile for Render Free
FROM php:8.3-apache

# Install PHP extensions and system dependencies
RUN apt-get update && apt-get install -y \
    unzip git libpq-dev libzip-dev libonig-dev libpng-dev libjpeg-dev libfreetype6-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install pdo pdo_pgsql zip gd

# Enable Apache modules
RUN a2enmod rewrite headers

# Set working directory
WORKDIR /var/www/html

# Copy Laravel application
COPY . .

# Install Composer (from composer official image)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install PHP dependencies WITHOUT caching config
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Fix permissions
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 777 storage bootstrap/cache

# Copy Apache config
COPY docker/000-default.conf /etc/apache2/sites-available/000-default.conf

# IMPORTANT — NO artisan commands before environment variables exist
# (Render injects envs only at runtime)

EXPOSE 80

# Run migrations at runtime (optional)
# CMD php artisan migrate --force && apache2-foreground

CMD apache2-foreground
