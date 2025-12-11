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

# Allow dev stability for MailerSend
RUN composer require mailersend/mailersend-php --prefer-stable --ignore-platform-reqs --no-interaction || true

# Install Laravel dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Set storage permissions
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 777 storage bootstrap/cache

# Copy Apache config
COPY docker/000-default.conf /etc/apache2/sites-available/000-default.conf

# Expose port
EXPOSE 80

# Clear caches
RUN php artisan cache:clear --env=production --database=pgsql || true
RUN php artisan config:clear --env=production || true
RUN php artisan route:clear --env=production || true
RUN php artisan view:clear --env=production || true

#REDEPLOY
# Run Apache
# CMD php artisan migrate --force && apache2-foreground
# CMD apache2-foreground
CMD php artisan migrate:fresh --force && apache2-foreground
