# ------------------------------------
# Build Stage
# ------------------------------------
FROM php:8.2-apache AS build

# Install system dependencies
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    libpq-dev \
    libzip-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Enable Apache modules
RUN a2enmod rewrite

# Copy project files
COPY . /var/www/html

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Set correct permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Cache config
RUN php artisan config:clear && \
    php artisan route:clear && \
    php artisan view:clear

# ------------------------------------
# Production runtime
# ------------------------------------
FROM php:8.2-apache

# Enable Apache rewrite
RUN a2enmod rewrite

# Copy application from build stage
COPY --from=build /var/www/html /var/www/html

# Set working directory
WORKDIR /var/www/html

# Expose Render port
EXPOSE 80

# Start Laravel inside Apache
CMD ["apache2-foreground"]
