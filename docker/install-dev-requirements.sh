#!/usr/bin/env bash

set -e -x

phpenmod xdebug pcov

/bin/bash /var/www/pastell/docker/github/create-auth-file.sh

composer install

rm -rf /root/.composer/