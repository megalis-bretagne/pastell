#!/bin/bash

set -e -x

export DEBIAN_FRONTEND=noninteractive

apt-get update

apt-get -y dist-upgrade

apt-get install -y --no-install-recommends \
    apache2 \
    ca-certificates \
    cron \
    curl \
    graphviz \
    language-pack-fr \
    php \
    php-bcmath \
    php-curl \
    php-imap \
    php-ldap \
    php-mbstring \
    php-mysql \
    php-redis \
    php-ssh2 \
    php-soap \
    php-xdebug \
    php-xml \
    php-zip \
    supervisor \
    unzip \
    wget \
    xmlstarlet

rm -r /var/lib/apt/lists/*

a2enmod \
    proxy \
    proxy_http \
    rewrite \
    ssl

echo 'LANG="fr_FR.UTF-8"'>/etc/default/locale
dpkg-reconfigure --frontend=noninteractive locales
update-locale LANG=fr_FR.UTF-8

echo "extension=pcov.so" > /etc/php/7.2/mods-available/pcov.ini
phpenmod pcov

rm /etc/localtime && ln -s /usr/share/zoneinfo/Europe/Paris /etc/localtime

# Composer installation
cd /tmp/
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php --install-dir=/usr/local/bin
mv /usr/local/bin/composer.phar /usr/local/bin/composer

# Libersign v1 stuff TODO
mkdir -p /var/www/parapheur/libersign
cd /var/www/parapheur/libersign/
wget https://ressources.libriciel.fr/s2low/libersign_v1_compat.tgz
tar xvzf libersign_v1_compat.tgz
