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

# Give the app user ownership of the working dir before switching to it,
# so Composer can create the vendor/ directory during the build.
RUN chown -R app:app /var/www

# Install PHP dependencies during the build, as the app user so that the
# generated vendor/ is owned correctly. The vendor/ baked into the image is
# preserved at runtime by an anonymous volume (see docker-compose.yml),
# so a fresh clone works without any manual "composer install" step.
USER app

COPY --chown=app:app composer.json composer.lock ./
RUN composer install --no-interaction --prefer-dist --no-scripts --no-autoloader

COPY --chown=app:app . .
RUN composer dump-autoload --optimize --no-scripts

EXPOSE 9000

CMD ["php-fpm"]
