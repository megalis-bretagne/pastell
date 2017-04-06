FROM php:5.6

MAINTAINER Eric Pommateau <eric.pommateau@libriciel.coop>

RUN docker-php-ext-install pdo pdo_mysql

