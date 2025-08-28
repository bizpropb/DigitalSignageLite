FROM php:8.3-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libpq-dev \
    libicu-dev \
    libzip-dev \
    && docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd intl zip

# Get Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install Node.js for Vite
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# Set working directory
WORKDIR /var/www/html

# Copy composer files
COPY composer.json ./

# Copy composer files for dependency installation
# Dependencies will be installed after copying full application

# Copy package.json files  
COPY package.json package-lock.json* ./

# Install Node dependencies
RUN npm install

# Copy application code
COPY . .

# Update composer dependencies and install new packages
RUN composer update --optimize-autoloader

# Publish Reverb configuration
RUN php artisan vendor:publish --provider="Laravel\Reverb\ReverbServiceProvider" --tag="reverb-config" || true

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 8000 8080

# Start PHP development server with migrations, Reverb WebSocket server, and queue worker
CMD php artisan migrate --force && php artisan reverb:start --host=0.0.0.0 --port=8080 & php artisan queue:work --daemon & php artisan serve --host=0.0.0.0 --port=8000