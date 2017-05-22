# Guide de migration depuis la version 1 de l'API vers la version 2


## Correspondance des appels depuis la V1 vers la V2

- version.php : GET /version 
- list-roles.php : GET /role 
- document-type.php : GET /flux  
- document-type-info.php : GET /flux/:id_flux 
- document-type-action.php : GET /flux/:id_flux/action
- list-extension.php : GET /extension
- edit-extension.php : POST /extension ou PUT /extension/:id_extension
- delete-extension.php : DELETE /extension/:id_extension
- journal.php : GET /journal 
- list-utilisateur.php : GET /utilisateur
- detail-utilisateur.php : GET /utilisateur/:id_u
- modif-utilisateur :  PATCH /utilisateur/:id_u
- create-utilisateur : POST /utilisateur
- delete-utilisateur : DELETE /utilisateur/:id_u
- list-role-utilisateur.php : GET /utilisateur/:id_u/role
- add-role-utilisateur.php : POST /utilisateur/:id_u/role
- delete-role-utilisateur.php : DELETE /utilisateur/:id_u/role
- list-entite.php : GET /entite
- detail-entite.php : GET /entite/:id_e
- modif-entite.php : PATCH /entite/:id_e
- delete-entite.php  DELETE /entite/:id_e
- create-entite.php : POST /entite
- list-connecteur-entite.php : GET /entite/:id_e/connecteur
- detail-connecteur-entite.php : GET /entite/:id_e/connecteur/:id_ce
- delete-connecteur-entite.php : DELETE /entite/:id_e/connecteur/:id_ce
- edit-connecteur-entite.php : PATCH /entite/:id_e/connecteur/:id_ce
- modif-connecteur-entite.php : PATCH /entite/:id_e/connecteur/:id_ce/content
- create-connecteur-entite.php : POST /entite/:id_e/connecteur/:id_ce


- list-flux-connecteur.php : GET /entite/:id_e/flux?type=:type&flux=:flux
- create-flux-connecteur.php : POST /entite/:id_e/flux/:id_f/connecteur/:id_ce
- delete-flux-connecteur.php : DELETE /entite/:id_e/flux/:id_f/connecteur/:id_ce
- action-connecteur-entite.php : POST /entite/:id_e/flux/:id_f/connecteur/:id_ce/action?action=:action&type=:type


TODO 
  

/entite/:id_e/document GET,POST
/entite/:id_e/document/:id_d GET,PUT,DELETE
/entite/:id_e/document?type=... 
/entite/:id_e/document/:id_d/:field_name/:number GET,POST,PUT


## Ressources qui n'ont pas leur équivalent en version 1.

- GET /extension/:id_extension
- GET /journal/:id_j 
- GET /journal/:id_j/jeton


## Fonctions manquantes dans la V2 pour que Pastell puisse être completement piloter via l'API

- POST /role 
- GET,PUT,DELETE /role/:id_role 
