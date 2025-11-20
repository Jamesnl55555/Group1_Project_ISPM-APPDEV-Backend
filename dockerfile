# Laravel SPA Backend Dockerfile for Render
FROM php:8.3-apache

# Install dependencies
RUN apt-get update && apt-get install -y \
    unzip git libpq-dev libzip-dev libonig-dev libpng-dev libjpeg-dev libfreetype6-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install pdo pdo_pgsql zip gd

# Enable Apache modules
RUN a2enmod rewrite headers

# Set working directory
WORKDIR /var/www/html

# Copy Laravel app
COPY . .

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Set permissions
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Cache config/routes/views
RUN php artisan config:cache \
 && php artisan route:cache \
 && php artisan view:cache

# Copy Apache config (point to /public)
COPY docker/000-default.conf /etc/apache2/sites-available/000-default.conf

# Expose port
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
