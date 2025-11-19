# 1️⃣ Use official PHP with Apache
FROM php:8.3-apache

# 2️⃣ Enable Apache mod_rewrite
RUN a2enmod rewrite

# 3️⃣ Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    sqlite3 \
    libsqlite3-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libpq-dev \
    pkg-config \
    curl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# 4️⃣ Install PHP extensions
RUN docker-php-ext-configure gd --with-jpeg --with-freetype
RUN docker-php-ext-install pdo pdo_mysql pdo_pgsql pdo_sqlite zip gd

# 5️⃣ Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 6️⃣ Set working directory
WORKDIR /var/www/html

# 7️⃣ Copy Laravel files
COPY . .

# 8️⃣ Set Apache document root to Laravel public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf

# 9️⃣ Install PHP dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# 🔹 Laravel cache & migrations
RUN php artisan config:cache
RUN php artisan route:cache
RUN php artisan migrate --force

# 10️⃣ Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# 11️⃣ Expose port 80 (Render maps $PORT automatically)
EXPOSE 80

# 12️⃣ Start Apache in foreground
CMD ["apache2-foreground"]
