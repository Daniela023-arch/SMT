FROM php:8.4-fpm-bookworm

RUN apt-get update && apt-get install -y \
    git unzip curl \
    libpq-dev libzip-dev libicu-dev \
    libonig-dev libpng-dev \
    && docker-php-ext-install \
    pdo_pgsql pgsql mbstring \
    bcmath zip intl gd opcache \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 \
    /usr/bin/composer /usr/bin/composer

RUN groupadd -g 1000 laravel \
    && useradd -u 1000 -g laravel \
    -m -s /bin/bash laravel

WORKDIR /var/www/html

USER laravel

EXPOSE 9000

CMD ["php-fpm"]