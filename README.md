[![Minimum PHP Version](http://img.shields.io/badge/php-%207.2-8892BF.svg)](https://php.net/)
[![License](https://img.shields.io/badge/licence-CeCILL%20v2-blue.svg)](http://www.cecill.info/licences/Licence_CeCILL_V2-fr.html)
[![pipeline status](https://gitlab.libriciel.fr/pastell/pastell/badges/master/pipeline.svg)](https://gitlab.libriciel.fr/pastell/pastell/commits/master)
[![coverage report](https://gitlab.libriciel.fr/pastell/pastell/badges/master/coverage.svg)](https://gitlab.libriciel.fr/pastell/pastell/commits/master)
[![Lignes de code](https://sonarqube.libriciel.fr/api/project_badges/measure?project=pastell&metric=ncloc)](https://sonarqube.libriciel.fr/dashboard?id=pastell)
[![Alerte](https://sonarqube.libriciel.fr/api/project_badges/measure?project=pastell&metric=alert_status)](https://sonarqube.libriciel.fr/dashboard?id=pastell)
[![Dette Technique](https://sonarqube.libriciel.fr/api/project_badges/measure?project=pastell&metric=sqale_index)](https://sonarqube.libriciel.fr/dashboard?id=pastell)

# Pastell

PASTELL est une solution libre et sécurisée,
 développée pour permettre le traitement sécurisé, automatisé et tracé 
 de l'ensemble des process dématérialisés.

## Configuration du Docker

Le docker est basé sur [php7-apache](https://hub.docker.com/_/php/).

Lors du démarrage, le Docker :
- crée ou met à jour la base de données ;
- crée un utilisateur admin si celui-ci n'existe pas ; 
- lance le démon Pastell.
 
### Variables d'environnement

| Variable d'environnement | Signification | Valeur par défaut |
|----| ---- | ---- |
| PASTELL_SITE_BASE | URL de base d'accès au site | http://localhost/ |
| MYSQL_USER | Login de l'utilisateur ayant accès à la base Pastell | user |
| MYSQL_PASSWORD | Mot de passe de cet utilisateur | user |
| MYSQL_HOST | Hôte de la base de données | localhost
| MYSQL_PORT | Port de connexion | 3306 |
| MYSQL_DATABASE | Nom de la base de données | pastell |
| REDIS_SERVER | Hote du serveur Redis | (vide) |
| REDIS_PORT | Port du serveur Redis | 6379 |
| XDEBUG_ON | Indique si on doit activer XDEBUG (développement/test seulement) | (vide) |
| PASTELL_ADMIN_LOGIN | Login de l'administrateur | admin |
| PASTELL_ADMIN_PASSWORD | Mot de passe de l'administrateur | admin |
| PASTELL_ADMIN_EMAIL | Email de l'administrateur | noreply@libriciel.coop |
| AUTHENTICATION_WITH_CLIENT_CERTIFICATE | Permettre la connexion par certificat (chaîne vide pour non, chaîne non-vide pour oui) | chaîne vide |


Volume : 

- /data/workspace : Répertoire de travail de Pastell
- /data/extensions: Répertoire des extensions Pastell
- /var/lib/php/session/ : Répertoire des sessions PHP (celles-ci ne sont pas nettoyer automatiquement)


## Utilisation via docker-compose

Mettre dans un fichier .env les variables du Docker et les variables suivantes :

| Variable d'environnement | Signification | Valeur par défaut |
|----| ---- | ---- |
| MYSQL_ROOT_PASSWORD | Mot de passe root de la base de données |
| WORKSPACE_VOLUME | Emplacement pour le répertoire de travail Pastell (workspace) |
| MYSQL_DATADIR | Emplacement de la bases MySQL |
| PASTELL_EXTENSION_PATH | Emplacement des extensions Pastell|

Démarrage : 
```
docker-compose -f docker-compose.yml up -d
```


Accès : http://localhost/ TODO : l'accès doit être relatif au PASTELL_SITE_BASE ...

## Utilisation de l'environnement de développement et de test via docker-compose


Ajouter les variables suivantes : 

| Variable d'environnement | Signification | Valeur par défaut |
| ----| ---- | ---- |
| MYSQL_HOST_TEST | Mot de passe root de la base de données (de test) | localhost |
| MYSQL_DATABASE_TEST | Nom de la base de test | pastell_test |
| MYSQL_USER_TEST | Login de l'utilisateur ayant accès à la base Pastell de test | user |
| MYSQL_PASSWORD_TEST | Mot de passe de cet utilisateur | user |


Démarrage : 
```
docker-compose up -d
```

- Accès au site : http://localhost:8000
- Accès à phpMyAdmin : http://localhost:8001 
- Accès au site de test (pour codeception): http://localhost:8003
- Accès à la base de données : mysql -u user -p pastell -h localhost -P8306

## Utilisation via PHPStorm

PHPStorm n'utilise pas docker-compose et écrase l'entrypoint lors du lancement de PHPUnit, 
il convient donc de spécifier les variables d'environnements directement dans la confiuguration du lanceur PHPUnit.
XDEBUG_ON ne peut pas être mis à (empty) dans ce cas-là.


## Utilisation via gitlab-ci

Gitlab-ci utilise l'entrypoint mais surcharge la commande.
