#!/bin/bash

chmod -R 777 /var/www/storage && chmod -R 777 /var/www/bootstrap/cache

#On error no such file entrypoint.sh, execute in terminal - dos2unix .docker\entrypoint.sh
cp .env.testing.exemple .env.testing
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate

php-fpm
