
## Authentification à l'API

L'authentification à l'API se fait soit : 


- via un certificat
- via le login/mot de passe Pastell. Celui-ci doit être passé via une authentification HTTP en mode BASIC
- via une connexion CAS, pour cela il faut ajouter un paramètre auth='cas' dans chacune des requête de l'API


## Paramètres d'entrée

Les paramètres peuvent être envoyés en GET ou en POST. Si des fichiers doivent être envoyés, alors
il faudra utiliser POST.

## Nom du script

Pour les version 1.X de Pastell, seul le nom du script était appelable (exemple : version.php). 

A partir de la version 2.0.0 de Pastell, on préfèrera utiliser les url sous la forme #URL de l'API#/Groupe/fonction.

Par exemple : https://pastell.adullact-projet.coop/api/Version/info est équivalent à 
https://pastell.adullact-projet.coop/api/version.php

Pour des raisons de compatibilité il est toujours possible d'appeller la fonction de l'API avec le nom du script
(version.php). Cette pratique est toutefois découragé et doit être considéré comme déprécié. L'utilisation du 
nom des scripts sera supprimé dans une prochaine version de Pastell.


