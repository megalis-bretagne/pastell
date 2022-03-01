#! /bin/bash

set -e -x

# Create the data directory
mkdir -p /data/{config,workspace,log,upload_chunk,html_purifier,certificate}
chown www-data: -R /data/

# needed for validca
mkdir -p /data/certificate/

# Configuration of different system part
cp /var/www/pastell/ci-resources/supervisord/*.conf /etc/supervisor/conf.d/
cp /var/www/pastell/ci-resources/logrotate.d/*.conf /etc/logrotate.d/
cp /var/www/pastell/ci-resources/cron.d/* /etc/cron.d/

# Set PHP configuration
mkdir -p /var/lib/php/session/
chown www-data: /var/lib/php/session

# TODO utiliser le phpenmod
cp /var/www/pastell/ci-resources/php/* /etc/php/8.1/cli/conf.d/
cp /var/www/pastell/ci-resources/php/* /etc/php/8.1/apache2/conf.d/


# needed for the composer install
mkdir -p /var/www/pastell/vendor/
chown www-data: /var/www/pastell/vendor/

# Apache configuration
cp /var/www/pastell/ci-resources/pastell-apache-config.conf /etc/apache2/sites-available/pastell-apache-config.conf
a2ensite pastell-apache-config.conf

# Create entrypoint command
cp /var/www/pastell/ci-resources/docker-pastell-entrypoint /usr/local/bin/
chmod a+x /usr/local/bin/docker-pastell-entrypoint
