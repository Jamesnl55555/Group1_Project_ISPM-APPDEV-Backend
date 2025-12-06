# Laravel SPA Backend Dockerfile for Render Free
FROM php:8.3-apache

# Install PHP extensions and dependencies
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

# Install MailerSend PHP SDK
RUN composer require mailersend/mailersend-php-sdk --prefer-stable

# Install Laravel dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Set storage permissions (Render Free requires 777 for sessions/logs)
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 777 storage bootstrap/cache

# Do NOT run php artisan config:cache at build — environment variables are injected at runtime

# Copy Apache config (points to /public)
COPY docker/000-default.conf /etc/apache2/sites-available/000-default.conf

# Expose port
EXPOSE 80

# Clear caches
RUN php artisan cache:clear --env=production --database=pgsql || true
RUN php artisan config:clear --env=production || true
RUN php artisan route:clear --env=production || true
RUN php artisan view:clear --env=production || true

# Run Apache
CMD apache2-foreground
