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