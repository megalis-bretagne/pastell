
Correspondance entre les anciens script de l'API version 1 et les nouveaux appel à l'API version 2

- version.php : GET /version 
- list-roles.php : GET /role 
- document-type.php : GET /flux  
- document-type-info.php : GET /flux/:id_flux 
- document-type-action.php : GET /flux/:id_flux/action
- list-extension.php : GET /extension
- edit-extension.php : POST /extension ou PUT /extension/:id_extension
- delete-extension.php : DELETE /extension/:id_extension
- journal.php : GET /journal 


Accès à des ressources qui n'ont pas leur équivalent en version 1.

- GET /extension/:id_extension
- GET /journal/:id_j 
- GET /journal/:id_j/jeton


TODO (compat)


/utilisateur GET,POST
/utilisateur/:id_u GET,PUT,DELETE

/utilisateur/:id_u/role GET,POST
/utilisateur/:id_u/role/:role GET,PUT,DELETE

/entite GET,POST
/entite/:id_e GET,PUT,DELETE

/entite/:id_e/connecteur GET,POST
/entite/:id_e/connecteur/:id_ce GET,PUT,DELETE
/entite/:id_e/connecteur/:id_ce/action/:action_name POST
/entite/:id_e/connecteur?flux=:id_f&type=:type

/entite/:id_e/flux/:id_f/connecteur/:id_ce POST,DELETE

/entite/:id_e/document GET,POST
/entite/:id_e/document/:id_d GET,PUT,DELETE
/entite/:id_e/document?type=... 
/entite/:id_e/document/:id_d/:field_name/:number GET,POST,PUT


TODO (other)
/role POST
/role/:id_role GET,PUT,DELETE
