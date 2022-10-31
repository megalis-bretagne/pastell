#!/bin/bash

set -e -x

if [ ${GID} -ne 33 ] ; then
  addgroup --gid "${GID}" "${GROUPNAME}"
fi

if [ ${UID} -ne 33 ] ; then
  adduser --uid "${UID}" --gid "${GID}" --gecos "" --disabled-password "${USERNAME}"
fi

a2enmod \
    headers \
    proxy \
    proxy_http \
    rewrite \
    ssl

echo 'LANG="fr_FR.UTF-8"'>/etc/default/locale
dpkg-reconfigure --frontend=noninteractive locales
update-locale LANG=fr_FR.UTF-8

rm /etc/localtime && ln -s /usr/share/zoneinfo/Europe/Paris /etc/localtime

chmod u+s /usr/sbin/cron