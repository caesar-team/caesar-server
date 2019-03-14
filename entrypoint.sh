#!/bin/sh

mkdir -p var/cache var/logs var/sessions
chown -R www-data: var
bin/console d:m:m -n
./bin/genkeys.sh
php-fpm -R
