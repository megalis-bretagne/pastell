FROM node:14-slim as node_modules
WORKDIR /var/www/pastell/
COPY package*.json ./
RUN npm install

FROM ubuntu:18.04 as pcov_ext
RUN apt-get update && DEBIAN_FRONTEND=noninteractive apt-get install -y php-dev
RUN pecl install pcov

FROM ubuntu:18.04 as pastell_base

ARG GITHUB_API_TOKEN
EXPOSE 443 80

WORKDIR /var/www/pastell/
ENV PATH="${PATH}:/var/www/pastell/vendor/bin/"

# Install requirements
COPY ./ci-resources/install-requirements.sh /var/www/pastell/ci-resources/
RUN /bin/bash /var/www/pastell/ci-resources/install-requirements.sh

COPY --from=composer:2.0 /usr/bin/composer /usr/bin/composer

# Create Pastell needs
COPY ./ci-resources /var/www/pastell/ci-resources
RUN /bin/bash /var/www/pastell/ci-resources/docker-construction.sh

COPY --chown=www-data:www-data --from=node_modules /var/www/pastell/node_modules /var/www/pastell/node_modules

# Composer stuff
COPY ./composer.* /var/www/pastell/

RUN /bin/bash /var/www/pastell/ci-resources/github/create-auth-file.sh && \
    /bin/bash -c 'mkdir -p /var/www/pastell/{web,web-mailsec}' && \
    composer install --no-dev --optimize-autoloader && \
    rm -rf /root/.composer/

# Pastell sources
COPY --chown=www-data:www-data ./ /var/www/pastell/

ENTRYPOINT ["docker-pastell-entrypoint"]
CMD ["/usr/bin/supervisord"]

FROM pastell_base as pastell_dev

COPY --from=pcov_ext /usr/lib/php/20170718/pcov.so /usr/lib/php/20170718/pcov.so
RUN /bin/bash /var/www/pastell/ci-resources/install-dev-requirements.sh

FROM pastell_base as pastell_prod