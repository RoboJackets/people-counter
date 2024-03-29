#!/bin/bash

cd "${0%/*}"

composer install --no-interaction --no-progress --no-dev --optimize-autoloader --classmap-authoritative
php artisan migrate --no-interaction --force
php artisan config:cache --no-interaction
php artisan view:cache --no-interaction
php artisan route:cache --no-interaction
php artisan nova:publish --no-interaction
php artisan horizon:publish --no-interaction
php artisan cache:clear --no-interaction

if [ -f ".last_deployment_hash" ]; then
    LAST_DEPLOYMENT=$(cat .last_deployment_hash)
else
    LAST_DEPLOYMENT=
fi

THIS_DEPLOYMENT=$(git rev-parse HEAD)

if [ "$LAST_DEPLOYMENT" == "" ] || [ "$THIS_DEPLOYMENT" == "$LAST_DEPLOYMENT" ] || git diff --name-only $LAST_DEPLOYMENT $THIS_DEPLOYMENT | grep -q '^package-lock\.json$'; then
    npm ci --no-progress
fi

if [ "$LAST_DEPLOYMENT" == "" ] || [ "$THIS_DEPLOYMENT" == "$LAST_DEPLOYMENT" ] || git diff --name-only $LAST_DEPLOYMENT $THIS_DEPLOYMENT | grep -q '^package-lock\.json$' || git diff --name-only $LAST_DEPLOYMENT $THIS_DEPLOYMENT | grep -qP '\.(?:js|vue|scss)$'; then
    npm run production --no-progress
fi

php artisan up
php artisan horizon:terminate
php artisan websockets:restart
#php artisan meilisearch:update-index-settings --only-return-id
