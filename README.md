[![Minimum PHP Version](http://img.shields.io/badge/php-%205.6-8892BF.svg)](https://php.net/)
[![License](https://img.shields.io/badge/licence-CeCILL%20v2-blue.svg)](http://www.cecill.info/licences/Licence_CeCILL_V2-fr.html)
[![build status](https://gitlab.libriciel.fr/pastell/pastell/badges/master/build.svg)](https://gitlab.libriciel.fr/pastell/pastell/commits/master)
[![coverage report](https://gitlab.libriciel.fr/pastell/pastell/badges/master/coverage.svg)](https://gitlab.libriciel.fr/pastell/pastell/commits/master)

Pastell



Variable d'environnement pour le Docker :
Mettre dans un fichier .env les variables suivantes :
- MYSQL_ROOT_PASSWORD=123456
- MYSQL_USER=user
- MYSQL_PASSWORD=user
- MYSQL_DATABASE=pastell
- MYSQL_HOST=db
- PASTELL_SITE_BASE=http://localhost:8000
- MYSQL_HOST_TEST=192.168.1.10
- MYSQL_DATABASE_TEST=pastell_test
- MYSQL_USER_TEST=user
- MYSQL_PASSWORD_TEST=user

Le docker est basé sur php7-apache.