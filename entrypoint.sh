#!/bin/sh

mkdir -p var/cache var/logs var/sessions
chown -R www-data: var
bin/console d:m:m -n
mkdir -p var/jwt
openssl genrsa -out var/jwt/private.pem -passout pass:$JWT_PASSPHRASE -aes256 4096
openssl rsa -pubout -in var/jwt/private.pem -passin pass:$JWT_PASSPHRASE -out var/jwt/public.pem

php-fpm -R
