# Migration de la version 1.4.6 vers la version 2.0.0.


## Procédure de migration

### Arreter le démon, le service Apache et la base de données : la migration ne peut pas être effectué à chaud


### Modifier le charset de la connexion à la base de données.

Cela doit-être fait dans le fichier LocalSettings.php

Exemple :
    define("BD_DSN","mysql:dbname=pastell;host=127.0.0.1;port=3306;charset=utf8");




## Liste des modification de l'API :

- suppression de la clé version-complete dans la fonction version.php
- suppresion de la fonction (cachée) external-data-controler.php




