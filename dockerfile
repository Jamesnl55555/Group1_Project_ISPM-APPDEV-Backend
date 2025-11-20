# ==========================
# Laravel SPA Backend Dockerfile for Render
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

# Enable Apache modules
RUN a2enmod rewrite && a2enmod headers

# --------------------------
# Set working directory
# --------------------------
WORKDIR /var/www/html

# --------------------------
# Copy Laravel app
# --------------------------
COPY . .

# --------------------------
# Install Composer dependencies
# --------------------------
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader --no-interaction

# --------------------------
# Ensure storage & cache directories exist
# --------------------------
RUN mkdir -p storage/logs \
    && mkdir -p storage/framework/cache \
    && mkdir -p storage/framework/sessions \
    && mkdir -p storage/framework/views \
    && mkdir -p bootstrap/cache

# --------------------------
# Permissions (correct for Render)
# --------------------------
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# --------------------------
# Cache config/routes/views
# --------------------------
RUN php artisan config:cache || true \
    && php artisan route:cache || true \
    && php artisan view:cache || true

# --------------------------
# Use your Apache config
# --------------------------
COPY docker/000-default.conf /etc/apache2/sites-available/000-default.conf

# --------------------------
# Expose port 80
# --------------------------
EXPOSE 80

CMD ["apache2-foreground"]
