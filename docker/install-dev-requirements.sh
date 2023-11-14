#!/usr/bin/env bash

set -e -x

export DEBIAN_FRONTEND=noninteractive

apt-get update

apt-get install -y --no-install-recommends \
    bash-completion \
    npm \
    php-pcov \
    php-xdebug

rm -r /var/lib/apt/lists/*

phpenmod xdebug pcov

/bin/bash /var/www/pastell/docker/github/create-auth-file.sh

composer install

rm -rf /root/.composer/

cp /var/www/pastell/docker/bash_completion.d/* /etc/bash_completion.d/
