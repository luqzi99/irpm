FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    libzip-dev \
    zip \
    unzip

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql pgsql mbstring exif pcntl bcmath gd zip

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy composer files first
COPY composer.json ./

# Install composer dependencies with PHP 8.2 compatibility
# Use composer update to regenerate lock file with compatible versions
RUN composer config platform.php 8.2.28 && \
    composer update --no-interaction --no-dev --no-scripts --optimize-autoloader

# Copy the rest of the application (excludes vendor and composer.lock via .dockerignore)
COPY . /var/www

# Install dependencies fresh (composer.lock excluded, will generate new compatible one)
RUN composer config platform.php 8.2.28 && \
    composer update --no-interaction --no-dev --optimize-autoloader

# Change ownership
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Expose port 9000
EXPOSE 9000

CMD ["php-fpm"]
