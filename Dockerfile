FROM php:8.4-fpm

# 1. System dependencies
RUN apt-get update && apt-get install -y \
    git unzip curl libzip-dev \
    && docker-php-ext-install zip pdo pdo_mysql

# 2. Node.js (for Vite)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# 3. Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 4. App directory
WORKDIR /var/www/html

# 5. Copy project files
COPY . .

# 6. Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# 7. Build frontend assets
RUN npm install && npm run build

# 8. Ensure Laravel storage directories exist and are writable
RUN mkdir -p storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Remove any cached Laravel config/views/routes
RUN rm -f bootstrap/cache/*.php

# 9. Start Laravel using Render's assigned port
CMD php -S 0.0.0.0:${PORT:-10000} -t public public/index.php
