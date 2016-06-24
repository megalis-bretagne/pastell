
L'authentification à l'API se fait soit : 


- via un certificat
- via le login/mot de passe Pastell. Celui-ci doit être passé via une authentification HTTP en mode BASIC
- via une connexion CAS, pour cela il faut ajouter un paramètre auth='cas' dans chacune des requête de l'API



## Paramètres d'entrée

Les paramètres peuvent être envoyés en GET ou en POST. Si des fichiers doivent être envoyés, alors
il faudra utiliser POST.

