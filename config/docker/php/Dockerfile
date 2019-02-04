FROM php:7.2-fpm

RUN apt-get update && apt-get install -y \
    git \
    libicu-dev \
    libpq-dev \
    locales \
    locales-all \
    zlib1g-dev

RUN docker-php-ext-install \
    bcmath \
    intl \
    opcache \
    pdo \
    pdo_pgsql \
    zip

#Liip Imagine support
RUN apt-get install -y \
        jpegoptim \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
        libpng-dev \
        optipng \
        libgpgme11-dev \
    && pecl install mcrypt-1.0.1; docker-php-ext-enable mcrypt \
    && docker-php-ext-install -j$(nproc) iconv \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd

ENV COMPOSER_ALLOW_SUPERUSER 1
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer; \
    chmod +x /usr/local/bin/composer

RUN pecl install gnupg

RUN usermod -s /bin/bash www-data; \
    touch /var/www/.bash_history; \
    chmod 777 /var/www/.bash_history

RUN chown -R www-data:www-data /usr/local/etc/php/; \
    chmod -R 775 /usr/local/etc/php/
RUN chown -R www-data:www-data /var/www

ENV PROMPT_COMMAND="history -a"
ENV HISTTIMEFORMAT="%h %d %H:%M:%S "
ENV HISTFILESIZE=20000
ENV LC_ALL en_US.UTF-8
ENV LANG en_US.UTF-8
ENV LANGUAGE en_US.UTF-8

COPY ./symfony.ini /usr/local/etc/php/conf.d
COPY ./symfony.pool.conf /usr/local/etc/php-fpm.d/
