# ---- Base Image ----
FROM php:7.4-fpm-alpine AS base
RUN mkdir -p /var/www/html /var/www/html/public/static /var/www/html/var/cache /var/www/html/var/logs /var/www/html/var/sessions /var/www/html/var/jwt && chown -R www-data /var/www/html
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
    rabbitmq-c \
    rabbitmq-c-dev \
    zip

RUN docker-php-ext-install \
    intl \
    bcmath\
    opcache \
    pdo \
    pdo_pgsql \
    zip \
    sockets

RUN pecl install gnupg redis amqp \
    && docker-php-ext-enable redis amqp

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

# ---- Test ----
FROM base AS test
ENV PGDATA /var/lib/postgresql/data
ENV POSTGRES_USER=test
ENV POSTGRES_DB=test
ENV POSTGRES_PASSWORD=test
ENV TEST_POSTGRES_USER=test
ENV TEST_POSTGRES_PASSWORD=test
ENV TEST_DATABASE_HOST=127.0.0.1
ENV TEST_POSTGRES_DB=test

RUN apk --update add su-exec bash postgresql postgresql-client
# this 777 will be replaced by 700 at runtime (allows semi-arbitrary "--user" values)
RUN mkdir -p "$PGDATA" \
    && chown -R postgres:postgres "$PGDATA" \
    && chmod 777 "$PGDATA" \
    && mkdir /docker-entrypoint-initdb.d
COPY tests/_scripts/init_db.sh /usr/local/bin
COPY tests/_scripts/wait-for-it.sh /usr/local/bin

COPY . .
RUN APP_ENV=test composer install
RUN vendor/bin/php-cs-fixer fix --config=.php_cs.dist -v --dry-run --using-cache=no

RUN bash init_db.sh postgres & wait-for-it.sh 127.0.0.1:5432 -- echo "postgres is up" \
    && bin/console doctrine:migrations:migrate --env=test --no-interaction \
    && vendor/bin/codecept build --no-interaction \
    && vendor/bin/codecept run unit \
    && vendor/bin/codecept run api

## ---- Webpack Encore ----
FROM node:10-alpine AS yarn-enc
COPY . .
RUN yarn install && yarn encore production
## ---- Dependencies ----
FROM base AS dependencies
## install vendors
USER www-data
RUN APP_ENV=prod composer install --prefer-dist --no-plugins --no-scripts --no-dev --optimize-autoloader
#
## ---- Release ----
FROM base AS release
EXPOSE 9000
USER www-data
ENV APP_ENV=prod
## copy production vendors
COPY --chown=www-data:www-data . .
COPY --chown=www-data:www-data --from=dependencies /var/www/html/vendor /var/www/html/vendor
COPY --from=yarn-enc ./public/build /var/www/html/public/build
COPY ./config/docker/php/symfony.ini /usr/local/etc/php/conf.d
## COPY ./config/docker/php/symfony.pool.conf /usr/local/etc/php-fpm.d/
COPY --chown=www-data:www-data entrypoint.sh /usr/local/bin/
RUN php bin/console assets:install public
## expose port and define CMD
ENTRYPOINT ["entrypoint.sh"]