FROM ubuntu:14.04
MAINTAINER Eric Pommateau <eric.pommateau@libriciel.coop>

RUN locale-gen fr_FR.UTF-8 &&\
    update-locale LANG=fr_FR.UTF-8 LC_MESSAGES=POSIX &&\
    echo "Europe/Paris" > /etc/timezone &&\
    dpkg-reconfigure -f noninteractive tzdata

RUN apt-get update && DEBIAN_FRONTEND=noninteractive && apt-get -qq upgrade && DEBIAN_FRONTEND=noninteractive apt-get -qq install \
    curl php5 php-soap php5-imap php5-xsl php5-curl php-pear php-apc \
    php5-gd php-mdb2-driver-mysql libssh2-php libapache2-mod-php5 php5-ldap graphviz xmlstarlet subversion

#TODO mettre uniquement en dev ?
RUN apt-get install php5-xdebug

RUN pear install XML_RPC2
RUN pear install https://developer.jasig.org/cas-clients/php/current.tgz
RUN a2enmod rewrite ssl mpm_prefork
RUN a2dismod mpm_event
RUN php5enmod imap

RUN mkdir -p /data/workspace && chown www-data: /data/workspace/

VOLUME /data/workspace

EXPOSE 80


COPY ./ /var/www/pastell/
COPY ./ci-resources/LocalSettings.php /var/www/pastell/LocalSettings.php
RUN cd /var/www && chown -R www-data: pastell


COPY ./ci-resources/pastell-apache-config.conf /etc/apache2/sites-available/pastell-apache-config.conf
RUN a2ensite pastell-apache-config.conf

CMD ["/bin/bash","/var/www/pastell/ci-resources/apache-entrypoint.sh"]