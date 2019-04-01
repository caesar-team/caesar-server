#!/bin/sh
bin/console d:m:m -n
./bin/genkeys.sh
php-fpm -R
