#!/bin/sh
su www-data -s /bin/sh ./bin/genkeys.sh
su www-data -s /bin/sh ./bin/cache.sh
php-fpm -R
