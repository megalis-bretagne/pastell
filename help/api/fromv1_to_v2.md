
DONE

- version.php : GET /version 
- list-roles.php : GET /role 
- document-type.php : /flux GET 
- /document-type-info.php : /flux/:id_flux GET


TODO (compat)






/extension GET,POST
/extension/:id_extension GET,PUT,DELETE

/journal GET
/journal/:id_j GET

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