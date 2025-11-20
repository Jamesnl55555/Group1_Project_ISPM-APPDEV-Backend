# ==========================
# Laravel SPA Backend Dockerfile for Render Free Tier
# ==========================
FROM php:8.3-apache

# --------------------------
# Install system dependencies and PHP extensions
# --------------------------
RUN apt-get update && apt-get install -y \
        unzip \
        git \
        libpq-dev \
        libzip-dev \
        libonig-dev \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_pgsql zip gd

# Enable Apache rewrite
RUN a2enmod rewrite
RUN a2enmod headers

# --------------------------
# Set working directory
# --------------------------
WORKDIR /var/www/html

# --------------------------
# Copy Laravel app
# --------------------------
COPY . .

# --------------------------
# Install Composer (non-interactive)
# --------------------------
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader --no-interaction

# --------------------------
# Set permissions for Laravel
# --------------------------
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# --------------------------
# Cache config/routes/views
# --------------------------
RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# --------------------------
# Copy Apache config
# --------------------------
COPY docker/000-default.conf /etc/apache2/sites-available/000-default.conf

# --------------------------
# Expose port 80
# --------------------------
EXPOSE 80

# --------------------------
# Start Apache
# --------------------------
CMD ["apache2-foreground"]
