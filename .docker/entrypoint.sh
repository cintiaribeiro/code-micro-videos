#!/bin/bash

chmod -R 777 /var/www/backend/storage && chmod -R 777 /var/www/backend/bootstrap/cache

#On error no such file entrypoint.sh, execute in terminal - dos2unix .docker\entrypoint.sh
### FRONT-END
npm config set cache /var/www/.npm-cache --global
cd /var/www/frontend && npm install && cd ..

### BACK-END
cd backend 
if [ ! -f ".env.testing"]; then
    cp .env.testing.exemple .env.testing
fi
if [ ! -f ".env"]; then
    cp .env.example .env
fi
composer install
php artisan key:generate
php artisan migrate

php-fpm
