FROM php:8.2-fpm

# Ставим системные пакеты для расширений
RUN apt-get update && apt-get install -y libonig-dev libzip-dev unzip git curl && docker-php-ext-install pdo_mysql mbstring exif bcmath && rm -rf /var/lib/apt/lists/*

# Ставим Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
