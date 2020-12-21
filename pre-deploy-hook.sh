#!/bin/bash

cd "${0%/*}"

php artisan down --retry=60 || true

git rev-parse HEAD > .last_deployment_hash
