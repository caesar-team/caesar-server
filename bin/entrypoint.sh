#!/bin/sh
# su www-data -s /bin/sh ./bin/genkeys.sh
# su www-data -s /bin/sh ./bin/cache.sh
# su www-data -s /bin/sh -c "php-fpm -R"
./bin/genkeys.sh
./bin/cache.sh
php-fpm -F