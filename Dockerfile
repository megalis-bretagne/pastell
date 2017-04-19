FROM php:7.0-apache
MAINTAINER Eric Pommateau <eric.pommateau@libriciel.coop>

RUN apt-get update && apt-get install -y \
    graphviz \
    libc-client-dev \
    libkrb5-dev \
    libldb-dev \
    libldap2-dev \
    libssh2-1 \
    libssh2-1-dev \
    libxml2-dev \
    locales \
    ntp \
    unzip \
    wget \
   && rm -r /var/lib/apt/lists/*

# Gestion des locales
RUN sed -i -e 's/# fr_FR.UTF-8 UTF-8/fr_FR.UTF-8 UTF-8/' /etc/locale.gen
RUN echo 'LANG="fr_FR.UTF-8"'>/etc/default/locale
RUN dpkg-reconfigure --frontend=noninteractive locales
RUN update-locale LANG=fr_FR.UTF-8

#Xdebug, attention la version 2.5.2 est buggué...
RUN pecl install xdebug-2.5.1 && \
    docker-php-ext-enable xdebug

# Extensions PHP
RUN docker-php-ext-install \
    bcmath \
    pdo \
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

# Bibliothèque phpCAS
RUN wget  https://developer.jasig.org/cas-clients/php/current.tgz && \
    tar xvzf current.tgz && \
    mv CAS-1.3.5/CAS /usr/local/lib/php/ && \
    mv CAS-1.3.5/CAS.php /usr/local/lib/php/

# Installation de composer
RUN cd /tmp/ && \
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php composer-setup.php --install-dir=/usr/local/bin && \
    mv /usr/local/bin/composer.phar /usr/local/bin/composer

# Workspace
RUN mkdir -p /data/workspace && chown www-data: /data/workspace/
VOLUME /data/workspace

# Source de Pastell
COPY ./ /var/www/pastell/

# Installation des dépendances composer
RUN cd /var/www/pastell/ && \
    composer install --dev

RUN chown -R www-data: /var/www/pastell

# Configuration d'apache
COPY ./ci-resources/pastell-apache-config.conf /etc/apache2/sites-available/pastell-apache-config.conf
RUN a2ensite pastell-apache-config.conf
RUN a2enmod rewrite
EXPOSE 80

COPY ./ci-resources/docker-pastell-entrypoint /usr/local/bin/
RUN chmod a+x /usr/local/bin/docker-pastell-entrypoint

ENTRYPOINT ["docker-pastell-entrypoint"]
CMD ["apache2-foreground"]