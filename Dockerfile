# Stage 1: PHP & Laravel Build
FROM php:8.2-fpm-alpine AS php-base

# Install system dependencies
RUN apk add --no-cache \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    zip \
    unzip \
    git \
    sqlite \
    sqlite-dev \
    postgresql-dev \
    mysql-client \
    oniguruma-dev

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install \
    gd \
    pdo \
    pdo_sqlite \
    pdo_pgsql \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    opcache

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files
COPY composer.json composer.lock* ./

# Copy .env file (or .env.example if .env doesn't exist)
COPY .env.example .env

# Install PHP dependencies (skip scripts to avoid artisan errors)
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Copy application files
COPY . .

# Set permissions
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 storage bootstrap/cache

# Stage 2: Node.js for frontend assets
FROM node:18-alpine AS node-builder

WORKDIR /var/www/html

COPY package.json package-lock.json* ./
RUN npm install --legacy-peer-deps

COPY . .
RUN npm run build

# Stage 3: Final production image
FROM php:8.2-fpm-alpine

RUN apk add --no-cache \
    curl \
    libpng \
    libjpeg-turbo \
    freetype \
    sqlite \
    postgresql-libs \
    mysql-client \
    oniguruma \
    supervisor \
    nginx

# Copy pre-compiled PHP extensions from build stage
COPY --from=php-base /usr/local/lib/php/extensions /usr/local/lib/php/extensions
COPY --from=php-base /usr/local/etc/php/conf.d /usr/local/etc/php/conf.d

WORKDIR /var/www/html

# Copy from PHP build stage
COPY --from=php-base --chown=www-data:www-data /var/www/html /var/www/html

# Copy built assets from Node stage
COPY --from=node-builder --chown=www-data:www-data /var/www/html/public/build /var/www/html/public/build

# Copy nginx configuration
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/default.conf /etc/nginx/http.d/default.conf

# Copy supervisor configuration
COPY docker/supervisord.conf /etc/supervisord.conf

# Copy entrypoint script
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 8080

CMD ["/entrypoint.sh"]
