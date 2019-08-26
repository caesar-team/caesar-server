FROM php:7.3-cli as base
RUN mkdir -p /var/www/html /var/www/html/public/static /var/www/html/var/cache /var/www/html/var/logs /var/www/html/var/sessions && chown -R www-data /var/www/html

RUN apt-get update && apt-get install -y --no-install-recommends \
  autoconf \
  git \
  vim \
  libzip-dev \
  unzip \
  libgpgme11-dev \
  procps \
  postgresql-server-dev-all \
  inotify-tools

# Install PHP Extensions
RUN docker-php-ext-install zip bcmath \
  pdo \
  pdo_pgsql \
  && docker-php-ext-install opcache \
  && docker-php-ext-enable opcache

RUN pecl install gnupg redis && docker-php-ext-enable redis

# Composer part
COPY --from=composer /usr/bin/composer /usr/bin/composer
ENV COMPOSER_MEMORY_LIMIT -1
# ENV COMPOSER_ALLOW_SUPERUSER 1
RUN composer global require hirak/prestissimo  --prefer-dist --no-progress --no-suggest --optimize-autoloader --no-interaction --no-plugins --no-scripts

# Run in production mode
ENV APP_ENV=prod

# Download RoadRunner
ENV RR_VERSION 1.4.7
RUN mkdir /tmp/rr \
  && cd /tmp/rr \
  && echo "{\"require\":{\"spiral/roadrunner\":\"${RR_VERSION}\"}}" >> composer.json \
  && composer install \
  && vendor/bin/rr get-binary -l /usr/local/bin \
  && rm -rf /tmp/rr

# Copy RoadRunner config
COPY containers/roadrunner/config /etc/roadrunner

# Set working directory
WORKDIR /var/www/html

# ---- Dependencies ----
FROM base AS dependencies
USER www-data

# Copy project file
COPY composer.json .
COPY composer.lock .

RUN APP_ENV=prod composer install --prefer-dist --no-plugins --no-scripts --no-dev --optimize-autoloader

# ---- Release ----
FROM base AS release
# copy production vendors
COPY --chown=www-data:www-data . .
COPY --chown=www-data:www-data --from=dependencies /var/www/html/vendor /var/www/html/vendor
COPY containers/roadrunner/symfony.ini /usr/local/etc/php/conf.d

RUN php bin/console assets:install public

ENTRYPOINT ["/usr/local/bin/rr", "serve", "-d", "-c", "/etc/roadrunner/.rr.yaml"]
