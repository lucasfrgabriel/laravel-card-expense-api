FROM php:8.3-fpm

WORKDIR /var/www

RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    git \
    unzip \
    curl \
    sqlite3 \
    libsqlite3-dev \
    --no-install-recommends && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install zip \
    && docker-php-ext-install exif \
    && docker-php-ext-install bcmath \
    && docker-php-ext-install opcache \
    && docker-php-ext-install mbstring \
    && docker-php-ext-install pdo_sqlite

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

COPY . /var/www

RUN chown -R www-data:www-data /var/www/storage \
    && chown -R www-data:www-data /var/www/bootstrap/cache \
    && chown -R www-data:www-data /var/www/database \
    && chmod -R 775 /var/www/storage \
    && chmod -R 775 /var/www//bootstrap/cache \
    && chmod -R 775 /var/www/database

RUN composer install --no-dev --prefer-dist --optimize-autoloader --ignore-platform-reqs

EXPOSE 9000

CMD ["php-fpm"]
