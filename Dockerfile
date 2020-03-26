FROM php:7.2-apache-stretch
MAINTAINER Eric Pommateau <eric.pommateau@libriciel.coop>

ARG GITHUB_API_TOKEN

RUN echo 'deb http://ftp.debian.org/debian stretch-backports main' >  /etc/apt/sources.list.d/stretch.backport.list && \
    apt-get update && \
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
        zlib1g-dev && \
    apt-get install -y -t stretch-backports python-certbot-apache && \
    rm -r /var/lib/apt/lists/*

# Gestion des locales
RUN sed -i -e 's/# fr_FR.UTF-8 UTF-8/fr_FR.UTF-8 UTF-8/' /etc/locale.gen && \
    echo 'LANG="fr_FR.UTF-8"'>/etc/default/locale && \
    dpkg-reconfigure --frontend=noninteractive locales && \
    update-locale LANG=fr_FR.UTF-8

RUN pecl install \
    pcov \
    redis \
    xdebug

# Configuration extension IMAP (voir http://stackoverflow.com/a/38526260 )
RUN docker-php-ext-configure imap --with-kerberos --with-imap-ssl

RUN docker-php-ext-enable \
    opcache \
    pcov \
    redis \
    xdebug

# Extensions PHP
RUN docker-php-ext-install \
    bcmath \
    imap \
    #pdo \ => see https://github.com/docker-library/php/issues/620
    pdo_mysql \
    soap \
    xsl \
    zip

# Extension SSH2 (voir https://medium.com/php-7-tutorial/solution-how-to-compile-php7-with-ssh2-f23de4e9c319)
RUN cd /tmp && \
    wget https://github.com/Sean-Der/pecl-networking-ssh2/archive/php7.zip && \
    unzip php7.zip && \
    cd /tmp/pecl-networking-ssh2-php7 && \
    phpize && \
    ./configure && \
    make && \
    make install && \
    docker-php-ext-enable ssh2

# Extension LDAP
RUN ln -s /usr/lib/x86_64-linux-gnu/libldap.so /usr/lib/libldap.so && \
    ln -s /usr/lib/x86_64-linux-gnu/liblber.so /usr/lib/liblber.so && \
    docker-php-ext-install ldap

# Configuration de php
COPY ./ci-resources/php/* /usr/local/etc/php/conf.d/

# Installation de composer
RUN cd /tmp/ && \
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php composer-setup.php --install-dir=/usr/local/bin && \
    mv /usr/local/bin/composer.phar /usr/local/bin/composer

# php.ini
COPY ./ci-resources/php/*.ini /usr/local/etc/php/conf.d/

RUN mkdir -p /data/config/ && \
    mkdir -p /data/workspace && \
    chown www-data: /data/workspace/ && \
    mkdir -p /data/log && \
    touch /data/log/pastell.log && \
    chown -R www-data: /data/log/ &&\
    mkdir -p /data/upload_chunk/ && \
    chown www-data: /data/upload_chunk/ && \
    mkdir -p /data/html_purifier/ && \
    chown www-data: /data/html_purifier/ && \
    mkdir -p /var/lib/php/session/ && \
    chown www-data: /var/lib/php/session && \
    mkdir -p /etc/apache2/ssl/ && \
    mkdir -p /var/www/pastell/vendor/


# Répertoire de travail
WORKDIR /var/www/pastell/

COPY ./ci-resources/github/create-auth-file.sh /tmp/create-auth-file.sh

RUN /bin/bash /tmp/create-auth-file.sh

#Composer
RUN mkdir -p web/vendor/bootstrap && mkdir -p web-mailsec/
COPY ./composer.* /var/www/pastell/
RUN composer install
ENV PATH="${PATH}:/var/www/pastell/vendor/bin/"


# Source de Pastell
COPY --chown=www-data:www-data ./ /var/www/pastell/

# Module d'Apache
RUN a2enmod \
    proxy \
    proxy_http \
    rewrite \
    ssl


ENV PATH="${PATH}:/usr/local/lib/composer/vendor/bin"

EXPOSE 443 80

VOLUME /data/workspace

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



ENTRYPOINT ["docker-pastell-entrypoint"]
CMD ["/usr/bin/supervisord"]
