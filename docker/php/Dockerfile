FROM php:8.3-fpm

# Match the container user to the host user to avoid file permission issues.
ARG UID=1000
ARG GID=1000

RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libicu-dev \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure intl \
    && docker-php-ext-install \
    pdo_mysql \
    mbstring \
    bcmath \
    intl \
    zip \
    gd \
    exif \
    pcntl

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN groupadd -g ${GID} app \
    && useradd -u ${UID} -g app -m -s /bin/bash app

COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-custom.ini

WORKDIR /var/www

USER app

EXPOSE 9000

CMD ["php-fpm"]
