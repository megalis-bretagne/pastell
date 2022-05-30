FROM node:14-slim as node_modules
WORKDIR /var/www/pastell/
COPY package*.json ./
RUN npm install

# TODO il faudra passer en PHP 8.1 une fois que scoper suportera cette version
FROM php:7.4-cli as extensions_builder
WORKDIR /app

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    zip \
    && rm -rf /var/lib/apt/lists/*

RUN curl \
    --location \
    --output /usr/bin/php-scoper \
    --url https://github.com/humbug/php-scoper/releases/download/0.17.0/php-scoper.phar \
    && chmod +x /usr/bin/php-scoper

COPY ./extensions/pastell-depot-cmis/ /app/
RUN composer install --ignore-platform-reqs \
    && php-scoper add-prefix --force \
    && composer dump-autoload --working-dir=build

FROM ubuntu:22.04 as pastell_base

ARG GITHUB_API_TOKEN
ARG UID=33
ARG GID=33
ARG USERNAME=www-data
ARG GROUPNAME=www-data

EXPOSE 4443 8080

WORKDIR /var/www/pastell/
ENV PATH="${PATH}:/var/www/pastell/vendor/bin/"

# Install requirements
COPY ./ci-resources/install-requirements.sh /var/www/pastell/ci-resources/
RUN /bin/bash /var/www/pastell/ci-resources/install-requirements.sh

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Create Pastell needs
COPY ./ci-resources /var/www/pastell/ci-resources

RUN /bin/bash /var/www/pastell/ci-resources/docker-construction.sh

COPY --chown=${USERNAME}:${GROUPNAME} --from=node_modules /var/www/pastell/node_modules /var/www/pastell/node_modules

# Composer stuff
COPY ./composer.* /var/www/pastell/
RUN /bin/bash /var/www/pastell/ci-resources/github/create-auth-file.sh && \
    /bin/bash -c 'mkdir -p /var/www/pastell/{web,web-mailsec}' && \
    composer install --no-dev --no-autoloader && \
    rm -rf /root/.composer/

# Pastell sources
COPY --chown=${USERNAME}:${GROUPNAME} ./ /var/www/pastell/
COPY --chown=${USERNAME}:${GROUPNAME} --from=extensions_builder /app/build /var/www/pastell/extensions/pastell-depot-cmis/build

RUN chown ${USERNAME}:${GROUPNAME} /var/www/pastell/

RUN composer dump-autoload --no-dev --optimize

USER "${USERNAME}"

HEALTHCHECK CMD curl --fail -k https://localhost:4443/ || exit 1

ENTRYPOINT ["docker-pastell-entrypoint"]
CMD ["/usr/bin/supervisord"]

FROM pastell_base as pastell_dev

ARG UID=33
ARG GID=33
ARG USERNAME=www-data
ARG GROUPNAME=www-data

USER root
RUN /bin/bash /var/www/pastell/ci-resources/install-dev-requirements.sh
USER "${USERNAME}"
FROM pastell_base as pastell_prod
