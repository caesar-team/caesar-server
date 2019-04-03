# ---- Base Image ----
FROM php:7.3-fpm-alpine AS base
RUN mkdir -p /var/www/html && chown -R www-data /var/www/html
# Set working directory
WORKDIR /var/www/html

RUN apk --update add \
    build-base \
    autoconf \
    git \
    icu-dev \
    gpgme-dev \
    gpgme \
    libzip-dev \
    postgresql-dev \
    zip

RUN docker-php-ext-install \
    intl \
    bcmath\
    opcache \
    pdo \
    pdo_pgsql \
    zip

RUN pecl install gnupg redis \
    && docker-php-ext-enable redis

# Composer part
COPY --from=composer /usr/bin/composer /usr/bin/composer
ENV COMPOSER_MEMORY_LIMIT -1
ENV COMPOSER_ALLOW_SUPERUSER 1
RUN composer global require hirak/prestissimo  --prefer-dist --no-progress --no-suggest --optimize-autoloader --no-interaction --no-plugins --no-scripts

# Run in production mode
ENV APP_ENV=prod
# Copy project file
COPY composer.json .
COPY composer.lock .

# ---- Dependencies ----
FROM base AS dependencies
# install vendors
RUN APP_ENV=prod composer install --prefer-dist --no-plugins --no-scripts --no-dev --optimize-autoloader

# ---- Release ----
FROM base AS release
# copy production vendors
USER www-data
COPY . .
COPY --from=dependencies /var/www/html/vendor /var/www/html/vendor
COPY ./config/docker/php/symfony.ini /usr/local/etc/php/conf.d
COPY ./config/docker/php/symfony.pool.conf /usr/local/etc/php-fpm.d/
COPY entrypoint.sh /usr/local/bin/
RUN mkdir -p var/cache var/logs var/sessions
# Preparing
RUN php bin/console assets:install public
USER root
# expose port and define CMD
EXPOSE 9000
ENTRYPOINT ["entrypoint.sh"]
