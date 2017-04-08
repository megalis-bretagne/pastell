FROM php:5.6

MAINTAINER Eric Pommateau <eric.pommateau@libriciel.coop>

RUN docker-php-ext-install pdo pdo_mysql

ADD . /var/www/html

EXPOSE 8000


//TODO : https://writing.pupius.co.uk/apache-and-php-on-docker-44faef716150