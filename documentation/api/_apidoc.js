/**
 * @api {get} /version.php /Version/info
 * @apiName Information sur la version
 * @apiGroup Version
 * @apiVersion 1.0.0
 * @apiSuccess {string} version Numéro de version
 * @apiSuccess {string} revision Numéro de révision SVN
 * @apiSuccess {string} version_complete Concaténation de version et révision
 * @apiSuccess {string} version-complete Concaténation de version et révision
 *
 *
 * @apiSuccessExample {json} Success-Reponse:
 * 		{
	 * 			"version":"2.0.0",
	 *	 		"revision":"1791",
	 *	 		"version_complete":"Version 2.0.0 - R\u00e9vision  1791"
	 *	 		"version-complete":"Version 2.0.0 - R\u00e9vision  1791"
	 * 		}
 */



/**
 * @api {get} /detail-document.php /Document/detail
 * @apiDescription Récupère l'ensemble des informations sur un document Liste également les entités filles.
 * @apiGroup Document
 * @apiVersion 1.0.0
 * @apiParam {int} id_e requis Identifiant de l'entité (retourné par list-entite)
 * @apiParam {int} id_d requis Identifiant du document (retourné par list-entite)
 * @apiSuccess {Object[]} info Reprend les informations disponible sur list-document.php
 * @apiSuccess {Object[]} data Données issue du formulaire (voir document-type-info.php pour savoir ce qu'il est possible de récupérer)
 * @apiSuccess {Object[]} action_possible Liste des actions possible (exemple : modification, envoie-tdt, ...)
 * @apiSuccess {Object[]} action-possible Liste des actions possible (exemple : modification, envoie-tdt, ...)
 *
 */


/**
 * @api {get} /modif-utilisateur.php /Utilisateur/edit
 * @apiDescription Permet la modification d'un utilisateur soit par son identifiant, soit par son login.
 Dans le cas où ces deux paramètres sont renseignés, seul l'identifiant sera pris en compte.
 * @apiGroup Utilisateur
 * @apiVersion 1.0.0
 *
 * @apiParam {int} id_u Identifiant de l'utilisateur
 * @apiParam {string} login  Login de l'utilisateur
 * @apiParam {string} password Mot de passe de l'utilisateur
 * @apiParam {string} prenom Prénom de l'utilisateur
 * @apiParam {string} nom Nom de l'utilisateur
 * @apiParam {int} id_e Identifiant de la collectivité de base de l'utilisateur (défaut 0)
 * @apiParam {string} email Email de l'utilisateur
 * @apiParam {boolean} create Flag permettant la création de l'utilisateur si aucun autre utilisateur ne porte le même nom
 * 						(faux par défaut)
 *
 * @apiSuccess {string} result ok si tout est ok
 */



/**
 * @api {get} /recherche-document.php /Document/recherche
 * @apiDescription Recherche multi-critère dans la liste des documents
 * @apiGroup Document
 * @apiVersion 1.0.0
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
 * @api {get} /journal.php /Journal/list
 * @apiDescription Récupérer le journal
 * @apiGroup Journal
 * @apiVersion 1.0.0
 *
 * @apiParam {int} id_e Identifiant de l'entité (retourné par list-entite)
 * @apiParam {string} recherche Champs de recherche sur le contenu du message horodaté
 * @apiParam {string} id_user Identifiant de l'utilisateur
 * @apiParam {string} date_debut Date à partir de laquelle les informations sont récupérées.
 * @apiParam {string} date_fin Date au delà de laquelle les informations ne sont plus récupérées.
 * @apiParam {string} id_d Identifiant du document
 * @apiParam {string} type Type de document (retourné par document-type.php)
 * @apiParam {string} format Format du journal : json(par défaut) ou bien csv
 * @apiParam {int} offset Numéro de la première ligne à retourner (0 par défaut)
 * @apiParam {int} limit Nombre maximum de lignes à retourner (100 par défaut)
 * @apiParam {int} csv_entete_colonne indique si on doit afficher l'entete des colonne (false par défaut)
 *
 * @apiSuccess {objet[]} journal
 * @apiSuccess {int} id_j Numéro unique, auto-incrémentiel et sans trou du journal
 * @apiSuccess {string} type
 * 					1. Action sur un document
 *					2 : Notification
 * 					3 : Modification d'une entité
 * 					4 : Modification d'un utilisateur
 * 					5 : Mail sécurisé
 * 					6 : Connexion
 * 					7 : Consultation d'un document"
 * @apiSuccess {int} id_e Identifiant de l'entité
 * @apiSuccess {int} id_u Identifiant de l'utilisateur
 * @apiSuccess {sting} id_d Identifiant du document
 * @apiSuccess {string} action Action effectuée
 * @apiSuccess {string} message Message
 * @apiSuccess {string} date Date de l'ajout dans le journal (peut-être différents de l'horodatage)
 * @apiSuccess {string} preuve Contenu de la preuve. Peut être utilisé dans une application qui sait analyser les jetons d'horodatage.
 * @apiSuccess {string} date_horodatage Date récupéré dans le jeton d'horodatage.
 * @apiSuccess {string} message_horodate Message qui a été horodaté
 * @apiSuccess {string} titre Titre du document
 * @apiSuccess {string} document_type Type du document
 * @apiSuccess {string} denomination Nom de l'entité
 * @apiSuccess {string} nom Nom de l'utilisateur
 * @apiSuccess {string} prenom Prénom de l'utilisateur
 *
 */