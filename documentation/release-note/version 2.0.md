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

### Demander aux utiliateurs de mettre à jour leur mot de passe

Bien qu'il soit toujours possible de se logguer avec l'ancien mot de passe, la génération des mots de passe à été renforcée.
Il est donc vivement conseillé de demander à tous les utilisateurs de mettre à jour leur mot de passe afin de profiter
du renforcement de mot de passe ajouté dans la version 2.0.0. (Passage de CRYPT_MD5 à CRYPT_BLOWFISH)


## Liste des modifications de l'API :

- suppression de la clé version-complete dans la fonction version.php
- suppresion de la fonction (cachée) external-data-controler.php
- suppression de la fonction de l'API detail-entite spécifique Adullact => on prends la définition de la fonction BL.
    La fonctionnalité de l'API spécifique Adullact peut être trouvé sur list-connecteur-entite
- la fonction /Journal/list ne ramène plus la preuve (problème avec l'utf-8). Il faut utiliser /Journal/preuve pour 
    récupérer unitairement les fichiers de preuve.

    
### Elements dépréciés

Les élements dépréciés sont utilisables, mais il est possible que ceux-ci soient supprimés dans une futur version de Pastell

- les anciens noms des scripts de l'API (ex: version.php) devraient être remplacé par les nouveaux (ex: /Version/info)    
    
- La classe SSH doit être remplacé par la classe SFTP qui utilise une implémentation purement PHP 
de SSH (phpseclib). 
    

## Modification du fichier de configuration

### Supression

- DETAIL_ENTITE_API (suppression de la fonction spécifique Adullact)
- Fonction MimeCode (à remplacer par FileContentType)

### Changement du fichier DefaultSettings.php

- PID_FILE et DAEMON_LOG_FILE pointe désormais par défaut sur le workspace
 	
## Supression de fichiers de template du coeur 
Certain fichier de template ont été déplacé dans leur extension respective :

- ChoixClassification
- IParapheurSousType
- IParapheurType
- NomemclatureList
- NomemclatureListSelect
- SelectGFCCollectivite
- TypeMessage


## Ajout de flux 

Les modules suivants ne font plus partie d'une extension mais réintègre le coeur Pastell:

- actes (generique, automatique, CDG)
- helios (generique, automatique, PES_Retour)
- document a faire signer (was: document-cdg85)
- bon de commande (generique)


## Modifications impactantes
- iParapheur : le DossierID accepte désormais les accents.
Les flux avec accent envoyé avant une mise à jour ne pourront pas être récupérer

- Les méthodes surchargeant ChoiceActionExecutor::display() doivent renvoyé true, sinon, 
l'action considère un échec et redirige vers l'affichage du formulaire.