# Laravel SPA Backend Dockerfile for Render Free
FROM php:8.4-apache

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
# RUN composer require mailersend/mailersend-php --prefer-stable --ignore-platform-reqs --no-interaction || true
RUN composer require getbrevo/brevo-php --prefer-stable --ignore-platform-reqs --no-interaction

# Install Laravel dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Set storage permissions
# ---- ADDITION: Ensure Laravel can write logs & cache ----
RUN mkdir -p storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache
# ----------------------------------------------------------

# Copy Apache config
COPY docker/000-default.conf /etc/apache2/sites-available/000-default.conf

# Expose port
EXPOSE 80

# Clear caches
RUN php artisan cache:clear --env=production --database=pgsql || true
RUN php artisan config:clear --env=production || true
RUN php artisan route:clear --env=production || true
RUN php artisan view:clear --env=production || true
RUN php artisan vendor:publish --tag=cloudinary

#REDEPLOY
# Run Apache

# ===========
# storage does not clear every reset
CMD php artisan migrate --force && apache2-foreground
# worker
# CMD php artisan migrate --force && (php artisan queue:work --sleep=3 --tries=3 --timeout=90 &) && apache2-foreground
#===========
# storage clear every reset
# CMD php artisan migrate:fresh --force && apache2-foreground

#===========
# RUN chmod -R 777 storage bootstrap/cache
# CMD bash -c "until php artisan migrate:status >/dev/null 2>&1; do echo 'Waiting for DB...'; sleep 3; done; php artisan migrate --force; php artisan db:seed --class=ProductSeeder --force; apache2-foreground"