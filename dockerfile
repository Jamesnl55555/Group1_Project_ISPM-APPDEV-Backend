# Use official PHP 8.3 with Apache
FROM php:8.3-apache

# Enable Apache mod_rewrite for Laravel routing
RUN a2enmod rewrite

# Install system dependencies required by Laravel and PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    sqlite3 \
    libsqlite3-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libwebp-dev \
    pkg-config \
    libonig-dev \
    libxml2-dev \
    curl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Configure GD extension to support JPEG, PNG, WebP, FreeType
RUN docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg \
    --with-webp

# Install PHP extensions commonly required by Laravel
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    pdo_sqlite \
    zip \
    gd \
    mbstring \
    bcmath \
    exif \
    pcntl

# Install Composer from official Composer image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy Laravel project files into container
COPY . .

# Install PHP dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Cache Laravel configuration for production
RUN php artisan config:cache

# Ensure proper permissions for storage and bootstrap cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Set Apache document root to Laravel's public folder
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

# Expose port 8080 for Render
EXPOSE 8080

# Generate Laravel config cache
RUN php artisan config:cache

# Run migrations on deploy (force to bypass prod warning)
RUN php artisan migrate --force
