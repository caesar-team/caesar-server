# ---- Base Image ----
FROM 4xxi/php-pgsql:flex AS base
# Preparing
RUN mkdir -p /var/www/html && chown -R www-data /var && rm -rf /var/lib/apt/lists/* \
apt-get update && apt-get install -y --no-install-recommends libgpgme11-dev && pecl install gnupg redis && docker-php-ext-enable gnupg redis
# Set working directory
WORKDIR /var/www/html
# Run in production mode
ENV APP_ENV=prod
# Copy project file
COPY composer.json .
COPY composer.lock .

# ---- Dependencies ----
FROM base AS dependencies
# install vendors
RUN composer global require hirak/prestissimo  --prefer-dist --no-progress --no-suggest --optimize-autoloader  --no-interaction
RUN APP_ENV=prod composer install --prefer-dist --no-progress --no-suggest --no-interaction --optimize-autoloader --no-scripts
# copy production vendor aside

# ---- Release ----
FROM base AS release
# copy production vendors
COPY --from=dependencies /var/www/html/vendor ./vendor
COPY . .
COPY entrypoint.sh /usr/local/bin/
COPY ./www.conf /usr/local/etc/php-fpm.d/
RUN bin/console assets:install public
# expose port and define CMD
EXPOSE 9000
ENTRYPOINT ["entrypoint.sh"]
