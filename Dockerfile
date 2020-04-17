# ---- Base Image ----
FROM php:7.4-fpm-alpine AS base
RUN mkdir -p /var/www/html /var/www/html/public/static /var/www/html/var/cache /var/www/html/var/logs /var/www/html/var/sessions && chown -R www-data /var/www/html 
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
    zip \
    sockets

RUN pecl install gnupg redis \
    && docker-php-ext-enable redis

# Composer part
COPY --from=composer /usr/bin/composer /usr/bin/composer
ENV COMPOSER_MEMORY_LIMIT -1
# ENV COMPOSER_ALLOW_SUPERUSER 1
RUN composer global require hirak/prestissimo  --prefer-dist --no-progress --no-suggest --optimize-autoloader --no-interaction --no-plugins --no-scripts

# Run in production mode
ENV APP_ENV=prod
# Copy project file
COPY composer.json .
COPY composer.lock .

FROM node:8-alpine AS yarn-enc
COPY . .
RUN yarn install && yarn encore production
# ---- Dependencies ----
FROM base AS dependencies
# install vendors
USER www-data
RUN APP_ENV=prod composer install --prefer-dist --no-plugins --no-scripts --no-dev --optimize-autoloader

# ---- Release ----
FROM base AS release
EXPOSE 9000
USER www-data
# copy production vendors
COPY --chown=www-data:www-data . .
COPY --chown=www-data:www-data --from=dependencies /var/www/html/vendor /var/www/html/vendor
COPY --from=yarn-enc ./public/build /var/www/html/public/build
COPY ./config/docker/php/symfony.ini /usr/local/etc/php/conf.d
# COPY ./config/docker/php/symfony.pool.conf /usr/local/etc/php-fpm.d/
COPY --chown=www-data:www-data entrypoint.sh /usr/local/bin/
RUN php bin/console assets:install public
# expose port and define CMD
ENTRYPOINT ["entrypoint.sh"]
