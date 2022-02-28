#!/usr/bin/env bash

set -e -x

export DEBIAN_FRONTEND=noninteractive

apt-get update

apt-get install -y --no-install-recommends \
    php-pcov \
    php-xdebug

rm -r /var/lib/apt/lists/*

echo "extension=pcov.so" > /etc/php/8.1/mods-available/pcov.ini

phpenmod xdebug pcov

/bin/bash /var/www/pastell/ci-resources/github/create-auth-file.sh

composer install

rm -rf /root/.composer/