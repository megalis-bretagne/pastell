FROM php:7.0-apache-stretch
MAINTAINER Eric Pommateau <eric.pommateau@libriciel.coop>

RUN apt-get update && apt-get install -y \
    cron \
    graphviz \
    libc-client-dev \
    libkrb5-dev \
    libldb-dev \
    libldap2-dev \
    libssh2-1 \
    libssh2-1-dev \
    libxml2-dev \
    locales \
    logrotate \
    ntp \
    ssmtp \
    supervisor \
    unzip \
    wget \
    xmlstarlet \
    zlib1g-dev \
   && rm -r /var/lib/apt/lists/*

# Installation de certbot
RUN echo 'deb http://ftp.debian.org/debian stretch-backports main' >  /etc/apt/sources.list.d/stretch.backport.list
RUN apt-get update && apt-get install -y -t stretch-backports \
    python-certbot-apache

# Gestion des locales
RUN sed -i -e 's/# fr_FR.UTF-8 UTF-8/fr_FR.UTF-8 UTF-8/' /etc/locale.gen
RUN echo 'LANG="fr_FR.UTF-8"'>/etc/default/locale
RUN dpkg-reconfigure --frontend=noninteractive locales
RUN update-locale LANG=fr_FR.UTF-8

# Installation de xdebug
RUN pecl install xdebug-2.9.0 && \
    docker-php-ext-enable xdebug

#Redis
RUN pecl install redis && \
    docker-php-ext-enable redis

# Ajout des extensions déjà présente
RUN docker-php-ext-enable opcache


# Extensions PHP
RUN docker-php-ext-install \
    bcmath \
    #pdo \ => see https://github.com/docker-library/php/issues/620
    pdo_mysql \
    soap \
    zip

# Paquets PEAR
RUN pear install \
    Mail \
    Mail_mime \
    XML_RPC2

# Extension IMAP (voir http://stackoverflow.com/a/38526260 )
RUN docker-php-ext-configure imap --with-kerberos --with-imap-ssl \
    && docker-php-ext-install imap

# Extension SSH2 (voir https://medium.com/php-7-tutorial/solution-how-to-compile-php7-with-ssh2-f23de4e9c319)
RUN cd /tmp && \
    wget https://github.com/php/pecl-networking-ssh2/archive/refs/tags/RELEASE_1_2.zip && \
    unzip RELEASE_1_2.zip && \
    cd /tmp/pecl-networking-ssh2-RELEASE_1_2 && \
    phpize && \
    ./configure && \
    make && \
    make install && \
    docker-php-ext-enable ssh2

# Extension LDAP
RUN ln -s /usr/lib/x86_64-linux-gnu/libldap.so /usr/lib/libldap.so && \
    ln -s /usr/lib/x86_64-linux-gnu/liblber.so /usr/lib/liblber.so && \
    docker-php-ext-install ldap

# Bibliothèque phpCAS
RUN wget  https://github.com/apereo/phpCAS/archive/1.3.5.tar.gz && \
    tar zxvf 1.3.5.tar.gz && \
    mv phpCAS-1.3.5/source/CAS /usr/local/lib/php/ && \
    mv phpCAS-1.3.5/source/CAS.php /usr/local/lib/php/

# Configuration de php
COPY ./ci-resources/php/* /usr/local/etc/php/conf.d/



# Installation de composer
RUN cd /tmp/ && \
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php composer-setup.php --install-dir=/usr/local/bin && \
    mv /usr/local/bin/composer.phar /usr/local/bin/composer

# php.ini
COPY ./ci-resources/php/*.ini /usr/local/etc/php/conf.d/

# Répertoire de configuration de Pastell
RUN mkdir -p /data/config/

# Workspace
RUN mkdir -p /data/workspace && chown www-data: /data/workspace/

# Log
RUN mkdir -p /data/log && chown www-data: /data/log/

#Chunk
RUN mkdir -p /data/upload_chunk/ && \
        chown www-data: /data/upload_chunk/

#Sessions PHP
RUN mkdir -p /var/lib/php/session/ && \
    chown www-data: /var/lib/php/session

# Répertoire contenant les certificats
RUN mkdir -p /etc/apache2/ssl/


# Répertoire de travail
WORKDIR /var/www/pastell/

# Source de Pastell
COPY ./ /var/www/pastell/


# Module d'Apache
RUN a2enmod \
    proxy \
    proxy_http \
    rewrite \
    ssl


ENV PATH="${PATH}:/usr/local/lib/composer/vendor/bin"

EXPOSE 443 80

RUN chown -R www-data: /var/www/pastell

COPY ./ci-resources/supervisord/*.conf /etc/supervisor/conf.d/
COPY ./ci-resources/logrotate.d/*.conf /etc/logrotate.d/
COPY ./ci-resources/cron.d/* /etc/cron.d/


# Configuration d'apache
COPY ./ci-resources/pastell-apache-config.conf /etc/apache2/sites-available/pastell-apache-config.conf
RUN a2ensite pastell-apache-config.conf

COPY ./ci-resources/docker-pastell-entrypoint /usr/local/bin/
RUN chmod a+x /usr/local/bin/docker-pastell-entrypoint

# Pour libersign
RUN mkdir -p /var/www/parapheur/libersign
ADD https://ressources.libriciel.fr/s2low/libersign_v1_compat.tgz /var/www/parapheur/libersign
RUN cd /var/www/parapheur/libersign && tar xvzf libersign_v1_compat.tgz


#Composer
RUN composer install
ENV PATH="${PATH}:/var/www/pastell/vendor/bin/"



ENTRYPOINT ["docker-pastell-entrypoint"]
CMD ["/usr/bin/supervisord"]
