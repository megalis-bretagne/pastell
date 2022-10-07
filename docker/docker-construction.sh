#! /bin/bash

set -e -x

# Create the data directory
mkdir -p /data/{config,workspace,log,upload_chunk,html_purifier,certificate,run}
chown "${USERNAME}": -R /data/

# needed for validca
mkdir -p /data/certificate/

# Supervisord configuration
cp  /var/www/pastell/docker/supervisord/supervisord.conf /etc/supervisor/supervisord.conf


cp /var/www/pastell/docker/logrotate.d/*.conf /etc/logrotate.d/

# Crond configuration
for CRONFILE in /var/www/pastell/docker/cron.d/*
do
  sed -e "s/%USERNAME%/${USERNAME}/g" $CRONFILE > "/etc/cron.d/$(basename $CRONFILE)"
done
chmod 0644 /etc/cron.d/*


# Set PHP configuration
mkdir -p /var/lib/php/session/
chown "${USERNAME}": /var/lib/php/session

# TODO utiliser le phpenmod
cp /var/www/pastell/docker/php/* /etc/php/8.1/cli/conf.d/
cp /var/www/pastell/docker/php/* /etc/php/8.1/apache2/conf.d/


# needed for the composer install
mkdir -p /var/www/pastell/vendor/
chown "${USERNAME}": /var/www/pastell/vendor/

# Apache configuration
sed -e "s/%USERNAME%/$USERNAME/g"  -e "s/%GROUPNAME%/$GROUPNAME/g" /var/www/pastell/docker/apache/envvars > /etc/apache2/envvars
cp /var/www/pastell/docker/apache/site-available/pastell-apache-config.conf /etc/apache2/sites-available/pastell-apache-config.conf
cp /var/www/pastell/docker/apache/conf-available/libriciel-security.conf /etc/apache2/conf-available/libriciel-security.conf
a2enconf libriciel-security
a2ensite pastell-apache-config.conf
a2dissite 000-default.conf
mkdir /data/run/apache2
mkdir -p /data/lock/apache2
mkdir -p /data/log/apache2

chown "${USERNAME}": /data/run/apache2
chown "${USERNAME}": /data/lock/apache2
chown "${USERNAME}": /data/log/apache2


# Create entrypoint command
cp /var/www/pastell/docker/docker-pastell-entrypoint /usr/local/bin/
chmod a+x /usr/local/bin/docker-pastell-entrypoint

bash /var/www/pastell/docker/add-legacy-provider-to-openssl-v3.sh