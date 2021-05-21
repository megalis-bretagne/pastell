#!/bin/bash

set -e -x

# Debian stuff
echo 'deb http://ftp.debian.org/debian stretch-backports main' >  /etc/apt/sources.list.d/stretch.backport.list

apt-get update

apt-get install -y \
        cron \
        graphviz \
        libc-client-dev \
        libkrb5-dev \
        libldb-dev \
        libldap2-dev \
        libssh2-1 \
        libssh2-1-dev \
        libxml2-dev \
        libxslt-dev \
        locales \
        logrotate \
        ntp \
        ssmtp \
        supervisor \
        unzip \
        wget \
        xmlstarlet \
        zlib1g-dev

apt-get install -y -t stretch-backports python-certbot-apache

rm -r /var/lib/apt/lists/*

# Locale
sed -i -e 's/# fr_FR.UTF-8 UTF-8/fr_FR.UTF-8 UTF-8/' /etc/locale.gen
echo 'LANG="fr_FR.UTF-8"'>/etc/default/locale
dpkg-reconfigure --frontend=noninteractive locales
update-locale LANG=fr_FR.UTF-8


# Needed to install php-ldap
ln -s /usr/lib/x86_64-linux-gnu/libldap.so /usr/lib/libldap.so && \
ln -s /usr/lib/x86_64-linux-gnu/liblber.so /usr/lib/liblber.so

# PHP Stuff
pecl install \
      pcov \
      redis \
      xdebug

# Configuration extension IMAP (see http://stackoverflow.com/a/38526260 )
docker-php-ext-configure imap --with-kerberos --with-imap-ssl

docker-php-ext-enable \
    opcache \
    pcov \
    redis \
    xdebug

# ext pdo is no more nedeed, see https://github.com/docker-library/php/issues/620
docker-php-ext-install \
    bcmath \
    imap \
    ldap \
    pdo_mysql \
    soap \
    xsl \
    zip

# deprecated 3.0.4
# Intalling php-ssh2 extension
# see https://medium.com/php-7-tutorial/solution-how-to-compile-php7-with-ssh2-f23de4e9c319
cd /tmp
wget https://github.com/php/pecl-networking-ssh2/archive/refs/tags/RELEASE_1_2.zip && \
unzip RELEASE_1_2.zip
cd /tmp/pecl-networking-ssh2-RELEASE_1_2
phpize
./configure
make
make install
docker-php-ext-enable ssh2


# Libersign v1 stuff
mkdir -p /var/www/parapheur/libersign
cd /var/www/parapheur/libersign/
wget https://ressources.libriciel.fr/s2low/libersign_v1_compat.tgz
tar xvzf libersign_v1_compat.tgz
