FROM php:7.0-apache
MAINTAINER Eric Pommateau <eric.pommateau@libriciel.coop>

# Gestion du temps
RUN apt-get update && DEBIAN_FRONTEND=noninteractive apt-get install -y ntp

# Gestion des locales
RUN apt-get update && DEBIAN_FRONTEND=noninteractive apt-get install -y locales
RUN sed -i -e 's/# fr_FR.UTF-8 UTF-8/fr_FR.UTF-8 UTF-8/' /etc/locale.gen
RUN echo 'LANG="fr_FR.UTF-8"'>/etc/default/locale
RUN dpkg-reconfigure --frontend=noninteractive locales
RUN update-locale LANG=fr_FR.UTF-8

# Extensions PHP
RUN docker-php-ext-install pdo pdo_mysql bcmath
#Version 2.5.2 est buggué...
RUN pecl install xdebug-2.5.1
RUN docker-php-ext-enable xdebug

# Extension IMAP (voir http://stackoverflow.com/a/38526260 )
RUN apt-get update && apt-get install -y libc-client-dev libkrb5-dev && rm -r /var/lib/apt/lists/*
RUN docker-php-ext-configure imap --with-kerberos --with-imap-ssl \
    && docker-php-ext-install imap

# Extension SOAP
RUN apt-get update && apt-get install libxml2-dev
RUN docker-php-ext-install soap

# Extension SSH2 (voir https://medium.com/php-7-tutorial/solution-how-to-compile-php7-with-ssh2-f23de4e9c319)
RUN apt-get install -y libssh2-1-dev libssh2-1 wget unzip

RUN cd /tmp && wget https://github.com/Sean-Der/pecl-networking-ssh2/archive/php7.zip && unzip php7.zip
RUN cd /tmp/pecl-networking-ssh2-php7 && phpize && ./configure && make && make install
RUN docker-php-ext-enable ssh2

# Extension zip
RUN  docker-php-ext-install zip

# Extension LDAP
RUN apt-get install -y libldb-dev libldap2-dev
RUN ln -s /usr/lib/x86_64-linux-gnu/libldap.so /usr/lib/libldap.so \
    && ln -s /usr/lib/x86_64-linux-gnu/liblber.so /usr/lib/liblber.so
RUN docker-php-ext-install ldap

# Paquet PEAR
RUN pear install Mail
RUN pear install Mail_mime
RUN pear install XML_RPC2

# Paquet CAS
RUN wget  https://developer.jasig.org/cas-clients/php/current.tgz
RUN tar xvzf current.tgz
RUN mv CAS-1.3.5/CAS /usr/local/lib/php/
RUN mv CAS-1.3.5/CAS.php /usr/local/lib/php/

# Commande dot
RUN apt-get install -y graphviz

# Workspace
RUN mkdir -p /data/workspace && chown www-data: /data/workspace/
VOLUME /data/workspace

# Source de Pastell
COPY ./ /var/www/pastell/
COPY ./ci-resources/LocalSettings.php /var/www/pastell/LocalSettings.php
RUN cd /var/www && chown -R www-data: pastell

# Configuration d'apache
COPY ./ci-resources/pastell-apache-config.conf /etc/apache2/sites-available/pastell-apache-config.conf
RUN a2ensite pastell-apache-config.conf
RUN a2enmod rewrite
EXPOSE 80