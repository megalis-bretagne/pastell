#! /bin/bash

set -e -x

# Create the data directory
mkdir -p /data/{config,workspace,log,upload_chunk,html_purifier}
chown www-data: -R /data/

# needed for validca
mkdir -p /etc/apache2/ssl/

# Configuration of different system part
cp /tmp/ci-resources/supervisord/*.conf /etc/supervisor/conf.d/
cp /tmp/ci-resources/logrotate.d/*.conf /etc/logrotate.d/
cp /tmp/ci-resources/cron.d/* /etc/cron.d/

# Set PHP configuration
mkdir -p /var/lib/php/session/
chown www-data: /var/lib/php/session
# TODO utiliser le phpenmod
cp /tmp/ci-resources/php/* /etc/php/7.2/cli/conf.d/
cp /tmp/ci-resources/php/* /etc/php/7.2/apache2/conf.d/

# Composer installation
cd /tmp/
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php --install-dir=/usr/local/bin
mv /usr/local/bin/composer.phar /usr/local/bin/composer

# needed for the composer install
mkdir -p /var/www/pastell/vendor/
chown www-data: /var/www/pastell/vendor/

# Apache configuration
cp /tmp/ci-resources/pastell-apache-config.conf /etc/apache2/sites-available/pastell-apache-config.conf
a2enmod \
    proxy \
    proxy_http \
    rewrite \
    ssl
a2ensite pastell-apache-config.conf

# Create entrypoint command
cp /tmp/ci-resources/docker-pastell-entrypoint /usr/local/bin/
chmod a+x /usr/local/bin/docker-pastell-entrypoint
