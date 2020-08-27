FROM node:14-slim as node_modules
WORKDIR /var/www/pastell/
COPY package*.json ./
RUN npm install

FROM php:7.2-apache-stretch

ARG GITHUB_API_TOKEN
EXPOSE 443 80
VOLUME /data/workspace
WORKDIR /var/www/pastell/
ENV PATH="${PATH}:/var/www/pastell/vendor/bin/"

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
