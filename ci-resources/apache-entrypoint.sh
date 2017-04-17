#! /bin/bash

apachectl stop
rm -f /var/run/apache2/apache2.pid
apachectl -D FOREGROUND