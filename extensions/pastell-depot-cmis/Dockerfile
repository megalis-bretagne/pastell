FROM php:7.4-cli
WORKDIR /app

ARG PHP_SCOPER_VERSION=0.17.0

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN curl \
    --location \
    --output /usr/bin/php-scoper \
    --url https://github.com/humbug/php-scoper/releases/download/${PHP_SCOPER_VERSION}/php-scoper.phar \
  && chmod +x /usr/bin/php-scoper
