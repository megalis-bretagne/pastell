/**
	 * @api {get} /Document/list /Document/list
	 * @apiDescription Listes de documents Pastell d'une entité (was:  /list-document.php)
	 * @apiGroup Document
	 * @apiVersion 1.0.0
	 * @apiParam {int} id_e requis Identifiant de l'entité (retourné par list-entite)
	 * @apiParam {string} type requis Identifiant du type de flux (retourné par document-type)
	 * @apiParam {int} offset Numéro de la première ligne à retourner (0 par défaut)
	 * @apiParam {int} limit Nombre maximum de lignes à retourner (100 par défaut)
	 * @apiSuccess {Object[]} document Liste de document
	 * @apiSuccess {int} id_e Identifiant numérique de l'entité
	 * @apiSuccess {int} id_d Identifiant unique du document
	 * @apiSuccess {string} last-action Dernière action effectuée sur le document
	 * @apiSuccess {string} last_action_date Date de la dernière action
	 * @apiSuccess {string} type Type de document (identique à l'entrée)
	 * @apiSuccess {string} creation Date de création du document
	 * @apiSuccess {string} modification Date de dernière modification du document
	 * @apiSuccess {id_e[]} entite Contient la même chose que action_possible,
	 *                        cette sortie est déprécié et sera supprimé dans une prochaine version de Pastell
	 *
	 */
	 
	 
	 
	 /**
     	 * @api {get} /Document/detail /Document/detail
     	 * @apiName /Document/detail
     	 * @apiDescription Récupère l'ensemble des informations sur un document Liste également les entités filles. (was: /detail-document.php)
     	 * @apiGroup Document
     	 * @apiVersion 2.0.0
     	 * @apiParam {int} id_e requis Identifiant de l'entité (retourné par list-entite)
     	 * @apiParam {int} id_d requis Identifiant du document (retourné par list-entite)
     	 * @apiSuccess {Object[]} info Reprend les informations disponible sur list-document.php
     	 * @apiSuccess {Object[]} data Données issue du formulaire (voir document-type-info.php pour savoir ce qu'il est possible de récupérer)
     	 * @apiSuccess {Object[]} action_possible Liste des actions possible (exemple : modification, envoie-tdt, ...)
     	 *
     	 */
     	 
     	 
     	 
     	 /**
         	 * @api {get} /Document/detailAll /Document/detailAll
         	 * @apiDescription Récupère l'ensemble des informations sur plusieurs documents. (was: /detail-several-document.php)
         	 * @apiGroup Document
         	 * @apiVersion 2.0.0
         	 * @apiParam {int} id_e requis Identifiant de l'entité (retourné par list-entite)
         	 * @apiParam {int[]} id_d[] requis Tableau d'identifiants uniques de documents  (retourné par list-document.php)
         	 * @apiSuccess {Array[]} array Liste d'objet decrit dans la fonction detail-document.php
         	 * @apiSuccess {Object[]} info Reprend les informations disponible sur list-document.php
         	 * @apiSuccess {Object[]} data Données issue du formulaire (voir document-type-info.php pour savoir ce qu'il est possible de récupérer)
         	 * @apiSuccess {Object[]} action_possible Liste des actions possible (exemple : modification, envoie-tdt, ...)
         	 *
         	 */
         	 
         	 
         	 /**
             	 * @api {get} /Document/create /Document/create
             	 * @apiDescription Création d'un document (was: /create-document.php)
             	 * @apiGroup Document
             	 * @apiVersion 1.0.0
             	 * @apiParam {int} id_e requis Identifiant de l'entité (retourné par list-entite)
             	 * @apiParam {string} type requis Identifiant du type de flux (retourné par document-type)
             	 * @apiSuccess {string} id_d  Identifiant unique du document crée.
             	 *
             	 */
             	 
             	 
             	 /**
                 	 * @api {get} /Document/edit /Document/edit
                 	 * @apiDescription Modification d'un document (was : /modif-document.php)
                 	 * @apiGroup Document
                 	 * @apiVersion 1.0.0
                 	 *
                 	 * @apiParam {int} id_e requis Identifiant de l'entité (retourné par list-entite)
                 	 * @apiParam {int} id_d Identifiant du document
                 	 * @apiParam {string} data Toutes les clés correspondantes au clé du type de document.
                 	 *        Permet également l'enregistrement des fichiers.
                 	 *        data n'est pas le nom de la clé en mode REST, il faut utiliser id_e, id_d, ...
                 	 *
                 	 * @apiSuccess {string} result ok - si l'enregistrement s'est bien déroulé
                 	 * @apiSuccess {string} formulaire_ok 1 si le formulaire est valide, 0 sinon
                 	 * @apiSuccess {string} message Message complémentaire
                 	 *
                 	 */
                 	 
                 	 

	/**
	 * @api {get}  /Document/externalData /Document/externalData
	 * @apiDescription Récupération des choix possibles pour un champs "données externes" du document (was: /external-data.php)
	 * @apiGroup Document
	 * @apiVersion 1.0.0
	 * @apiParam {int} id_e requis Identifiant de l'entité (retourné par list-entite)
	 * @apiParam {int} id_d requis Identifiant du document (retourné par list-entite)
	 * @apiParam {string} field requis Identifiant du champs à récupérer
	 * @apiParam {string} type requis Identifiant du type de flux (retourné par document-type)
	 * @apiSuccess {variable} output  Information supplémentaire sur la valeur possible (éventuellement sous forme de tableau associatif)
	 *
	 */                 	 
	 

	/**
	 * @api {get}  /Document/recherche /Document/recherche
	 * @apiName /Document/recherche
	 * @apiDescription Recherche multi-critère dans la liste des documents (was: /recherche-document.php)
	 * @apiGroup Document
	 * @apiVersion 2.0.0
	 *
	 * @apiParam {int} id_e requis Identifiant de l'entité (retourné par list-entite)
	 * @apiParam {string} type Identifiant du type de flux (retourné par document-type)
	 * @apiParam {int} offset Numéro de la première ligne à retourner (0 par défaut)
	 * @apiParam {int} limit Nombre maximum de lignes à retourner (100 par défaut)
	 * @apiParam {string} lastetat Dernier état du document
	 * @apiParam {string} last_state_begin Date du passage au dernier état du document le plus ancien(date iso)
	 * @apiParam {string} last_state_end date du passage au dernier état du document le plus récent(date iso)
	 * @apiParam {string} etatTransit le document doit être passé dans cet état
	 * @apiParam {string} state_begin date d'entrée la plus ancienne de l'état etatTransit
	 * @apiParam {string} state_end date d'entrée la plus récente de l'état etatTransit
	 * @apiParam {string} tri critère de tri parmi last_action_date, title et entite
	 * @apiParam {string} search l'objet du document doit contenir la chaine indiquée
	 * @apiParam {string} date_in_fr Les dates sont spécifié au format jj/mm/yyyy au lieu de yyyy-mm-jj (NE PAS UTILISER)
	 *
	 * @apiSuccess {Object[]} document liste de documents pastell
	 * @apiSuccess {int} id_e requis Identifiant de l'entité
	 * @apiSuccess {string} id_d Identifiant unique du document
	 * @apiSuccess {string} role Rôle de l'entité sur le document (exemple : éditeur)
	 * @apiSuccess {string} last-action Dernière action effectuée sur le document
	 * @apiSuccess {string} last_action_date Date de la dernière action
	 * @apiSuccess {string} type Type de document (identique à l'entrée)
	 * @apiSuccess {string} creation Date de création du document
	 * @apiSuccess {string} modification Date de dernière modification du document
	 * @apiSuccess {int[]} entite Liste des identifiant (id_e) des autres entités qui ont des droits sur ce document
	 *
	 */	 
	 


	/**
	 * @api {get} /Document/recuperationFichier /Document/recuperationFichier
	 * @apiDescription Récupère le contenu d'un fichier (was : /recuperation-fichier.php)
	 * @apiGroup Document
	 * @apiVersion 1.0.0
	 *
	 * @apiParam {int} id_e requis Identifiant de l'entité (retourné par list-entite)
	 * @apiParam {int} id_d Identifiant du document
	 * @apiParam {string} field le nom du champs
	 * @apiParam {string} num numéro du fichier (pour les fichier multiple)
	 *
	 * @apiSuccess {raw} raw_data le contenu du fihcier
	 *
	 */
	 	 
	 	 
 /**
     * @api {get}  /Document/sendFile /Document/sendFile
     * @apiDescription Envoi d'un fichier sur un document (dans le postdata) (was: /send-file.php)
     * @apiGroup Document
     * @apiVersion 1.0.0
     *
     * @apiParam {int} id_e requis Identifiant de l'entité (retourné par list-entite)
     * @apiParam {int} id_d Identifiant du document
     * @apiParam {string} field_name le nom du champs
     * @apiParam {string} file_name le nom du fichier
     * @apiParam {string} file_number le numéro du fichier (pour les fichier multiple)
     * @apiParam {string} file_content le contenu du fichier
     *
     * @apiSuccess {string} result ok - si l'enregistrement s'est bien déroulé
     * @apiSuccess {string} formulaire_ok 1 si le formulaire est valide, 0 sinon
     * @apiSuccess {string} message Message complémentaire
     *
     */
     
   /**
   	 * @api {get} /Document/receiveFile /Document/receiveFile
   	 * @apiDescription Récupère le contenu d'un document (via JSON !) (DEPRECATED, ne plus utiliser) (was: /receive-file.php)
   	 *
   	 * @apiGroup Document
   	 * @apiVersion 1.0.0
   	 *
   	 * @apiParam {int} id_e requis Identifiant de l'entité (retourné par list-entite)
   	 * @apiParam {int} id_d Identifiant du document
   	 * @apiParam {string} field_name le nom du champs
   	 * @apiParam {string} file_number le numéro du fichier (pour les fichier multiple)
   	 *
   	 * @apiSuccess {string} file_name ok - le nom du fichier
   	 * @apiSuccess {string} file_content le contenu du fichier
   	 *
   	 *
   	 */  
   	 
/**
	 * @api {get} /Document/action /Document/action
	 * @apiDescription Execute une action sur un document (was: /action.php)
	 * @apiGroup Document
	 * @apiVersion 1.0.0
	 *
	 * @apiParam {int} id_e requis Identifiant de l'entité (retourné par list-entite)
	 * @apiParam {string} id_d Identifiant du document
	 * @apiParam {string} action Le nom de l'action
	 * @apiParam {string[]} destinataire Tableau contenant l'identifiant des destinataires pour les actions qui le requièrent
	 *
	 * @apiSuccess {int} result 1 si l'action a été correctement exécute. Sinon, une erreur est envoyé
	 * @apiSuccess {string} message "Message complémentaire en cas de réussite"
	 *
	 *
	 */   	 