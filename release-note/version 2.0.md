# Migration de la version 1.4.6 vers la version 2.0.0.


## Procédure de migration

### Arreter le démon, le service Apache et la base de données : la migration ne peut pas être effectué à chaud


### Modifier le charset de la connexion à la base de données.

Cela doit-être fait dans le fichier LocalSettings.php

Exemple :
    define("BD_DSN","mysql:dbname=pastell;host=127.0.0.1;port=3306;charset=utf8");


### Modifier la base de données

Avec le script dbupdate pour voir les requêtes à passer.

Attention : une modification sur la table journal : le champs preuve passe de text à blob (sinon, les jetons d'horodatage ne marche plus).



## Liste des modification de l'API :

- suppression de la clé version-complete dans la fonction version.php
- suppresion de la fonction (cachée) external-data-controler.php
- suppression de la fonction de l'API detail-entite spécifique Adullact => on prends la définition de la fonction BL.
    La fonctionnalité de l'API spécifique Adullact peut être trouvé sur list-connecteur-entite
- la fonction journal/list ne ramène plus la preuve (problème avec l'utf-8). Il faut utiliser /Journal/preuve pour 
    récupérer unitairement les fichiers de preuve.
    

## Modification du fichier de configuration

### Supression

- DETAIL_ENTITE_API (suppression de la fonction spécifique Adullact)

