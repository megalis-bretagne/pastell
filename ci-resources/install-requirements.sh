#!/bin/bash

set -e -x

export DEBIAN_FRONTEND=noninteractive

apt-get update

apt-get -y dist-upgrade

apt-get install -y --no-install-recommends \
    apache2 \
    ca-certificates \
    certbot \
    cron \
    curl \
    git \
    graphviz \
    language-pack-fr \
    msmtp \
    php \
    php-bcmath \
    php-curl \
    php-imap \
    php-intl \
    php-ldap \
    php-mbstring \
    php-mysql \
    php-redis \
    php-soap \
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

rm /etc/localtime && ln -s /usr/share/zoneinfo/Europe/Paris /etc/localtime
