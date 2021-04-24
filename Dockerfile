FROM node:14-slim as node_modules
WORKDIR /var/www/pastell/
COPY package*.json ./
RUN npm install

FROM ubuntu:18.04 as pcov_ext
RUN apt-get update && DEBIAN_FRONTEND=noninteractive apt-get install -y php-dev
RUN pecl install pcov


FROM ubuntu:18.04

ARG GITHUB_API_TOKEN
EXPOSE 443 80
VOLUME /data/workspace
WORKDIR /var/www/pastell/
ENV PATH="${PATH}:/var/www/pastell/vendor/bin/"

COPY --from=pcov_ext /usr/lib/php/20170718/pcov.so /usr/lib/php/20170718/pcov.so

# Install requirements
COPY ./ci-resources/install-requirements.sh /root/
RUN /bin/bash /root/install-requirements.sh

# Create Pastell needs
COPY ./ci-resources/ /tmp/ci-resources/
RUN /bin/bash /tmp/ci-resources/docker-construction.sh

COPY --chown=www-data:www-data --from=node_modules /var/www/pastell/node_modules /var/www/pastell/node_modules


# Composer stuff
COPY ./composer.* /var/www/pastell/
RUN /bin/bash /tmp/ci-resources/github/create-auth-file.sh && \
    /bin/bash -c 'mkdir -p /var/www/pastell/{web,web-mailsec}' && \
    composer install

# Pastell sources
COPY --chown=www-data:www-data ./ /var/www/pastell/

ENTRYPOINT ["docker-pastell-entrypoint"]
CMD ["/usr/bin/supervisord"]
