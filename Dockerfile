# ---- Based on TrafeX/docker-php-nginx ----
FROM php:7.4-fpm-alpine AS base
LABEL Maintainer="Aleksandr Beshkenade <ab@caesar.team>" \
      Description="Lightweight container with Nginx 1.18 & PHP-FPM 7.4 based on Alpine Linux."

RUN apk --update add \
    curl \
    nginx \
    supervisor \
    git \
    gpgme \
    rabbitmq-c \
    zip

RUN apk add --no-cache --no-progress --virtual BUILD_DEPS ${PHPIZE_DEPS}
RUN apk add --no-cache --no-progress --virtual BUILD_DEPS_PHP \
    rabbitmq-c-dev \
    libzip-dev \
    postgresql-dev \
    icu-dev \
    gpgme-dev

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

RUN apk del --no-progress BUILD_DEPS BUILD_DEPS
# Config part

# Configure nginx
COPY config/docker/nginx.conf /etc/nginx/nginx.conf

# Configure PHP-FPM
COPY config/docker/php/fpm-pool.conf /etc/php/php-fpm.d/www.conf
COPY config/docker/php/php.ini /etc/php/conf.d/custom.ini

# Configure supervisord
COPY config/docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Make sure files/folders needed by the processes are accessable when they run under the nobody user
RUN chown -R nobody.nobody /var/www/html && \
  chown -R nobody.nobody /run && \
  chown -R nobody.nobody /var/lib/nginx && \
  chown -R nobody.nobody /var/log/nginx && \
  mkdir -p /var/www/html /var/www/html/public/static && \
  mkdir -p /var/www/html/var/cache /var/www/html/var/logs && \
  mkdir -p /var/www/html/var/sessions /var/www/html/var/jwt && \
  chown -R nobody.nobody /var/www/html

# Composer part
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_MEMORY_LIMIT -1
ENV COMPOSER_ALLOW_SUPERUSER 0
# RUN composer global require hirak/prestissimo  --prefer-dist --no-progress --optimize-autoloader --no-interaction --no-plugins --no-scripts

# Switch to use a non-root user from here on
USER nobody
# Setup document root
WORKDIR /var/www/html

# Run in production mode
ENV APP_ENV=prod

# Copy project file
COPY composer.json .
COPY composer.lock .

# # ---- Test ----
# FROM base AS test
# ENV PGDATA /var/lib/postgresql/data
# ENV POSTGRES_USER=test
# ENV POSTGRES_DB=test
# ENV POSTGRES_PASSWORD=test
# ENV TEST_POSTGRES_USER=test
# ENV TEST_POSTGRES_PASSWORD=test
# ENV TEST_DATABASE_HOST=127.0.0.1
# ENV TEST_POSTGRES_DB=test

# RUN apk --update add su-exec bash postgresql postgresql-client
# # this 777 will be replaced by 700 at runtime (allows semi-arbitrary "--user" values)
# RUN mkdir -p "$PGDATA" \
#     && chown -R postgres:postgres "$PGDATA" \
#     && chmod 777 "$PGDATA" \
#     && mkdir /docker-entrypoint-initdb.d
# COPY tests/_scripts/init_db.sh /usr/local/bin
# COPY tests/_scripts/wait-for-it.sh /usr/local/bin

# COPY . .
# RUN APP_ENV=test composer install
# RUN vendor/bin/php-cs-fixer fix --config=.php_cs.dist -v --dry-run --using-cache=no

# RUN bash init_db.sh postgres & wait-for-it.sh 127.0.0.1:5432 -- echo "postgres is up" \
#     && bin/console doctrine:migrations:migrate --env=test --no-interaction \
#     && vendor/bin/codecept build --no-interaction \
#     && vendor/bin/codecept run unit \
#     && vendor/bin/codecept run api

## ---- Webpack Encore ----
FROM node:10-alpine AS yarn-enc
COPY . .
RUN yarn install && yarn encore production
## ---- Dependencies ----
FROM base AS dependencies
## install vendors
USER nobody
RUN APP_ENV=prod composer install --prefer-dist --no-plugins --no-scripts --no-dev --optimize-autoloader
#
## ---- Release ----
FROM base AS release
EXPOSE 9000
USER nobody
ENV APP_ENV=prod
## copy production vendors
COPY --chown=nobody:nobody . .
COPY --chown=nobody:nobody --from=dependencies /var/www/html/vendor /var/www/html/vendor
COPY --chown=nobody:nobody --from=yarn-enc ./public/build /var/www/html/public/build
RUN php bin/console assets:install public
## expose port and define CMD
# ENTRYPOINT ["entrypoint.sh"]

COPY --chown=nobody:nobody ./bin/entrypoint.sh /var/www/html/bin/entrypoint.sh
COPY --chown=nobody:nobody ./public/index.php /var/www/html/public/index.php
# Let supervisord start nginx & php-fpm
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]

# Configure a healthcheck to validate that everything is up&running
# HEALTHCHECK --timeout=10s CMD curl --silent --fail http://127.0.0.1:8080/fpm-ping