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