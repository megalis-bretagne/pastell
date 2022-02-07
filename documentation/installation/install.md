
# Installer la base de données

Le fichier pastell.sql contient le schéma de la base de données MySQL 

# Configurer l'application 

Le fichier DefaultSettings.php contient l'ensemble des valeurs qu'il est possible de configurer.
Il faut créer un fichier LocalSettings.php afin de mettre des valeurs correspondantes
à l'environnement de l'installation.

# Configurer le serveur web

Le serveur web doit "servir" le répertoire "web" (voir apache.conf pour un exemple)
Le serveur web doit "servir" aussi le répertoire "web-mailsec" 


# Créer un administrateur

Le script create-admin.php permet la création d'un administrateur initial

# Mettre en place un crontab 
50 4 * * * recup-classification.php

# Pour le module mail sécurisé
Créer un lien symbolique dans web-mailsec qui pointe vers web/img/
cd web-mailsec
ln -s ../web/img/

