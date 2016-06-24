<?php

class ExtensionController extends BaseAPIController {


	//FIXME inverser cette dépendance...
	private $systemControler;

	public function __construct(
		SystemControler $systemControler
	) {
		$this->systemControler = $systemControler;
	}

	/**
	 * @api {get} /edit-extension.php /Extension/edit
	 * @apiDescription Ajout ou modification du chemin d'une extension
	 * @apiGroup Extension
	 * @apiVersion 1.0.0
	 * @apiParam {string} id_extension Identifiant de l'extension à modifier, 0 ou rien pour créer une extension
	 * @apiParam {string} path Emplacement de l'extension sur le système de fichier
	 * @apiSuccess {string} result ok si tout est ok
	 */
	public function editAction(){
		$id_extension = $this->getFromRequest('id_extension');
		$path = $this->getFromRequest('path');
		$result['detail_extension'] = $this->systemControler->doExtensionEdition($id_extension,$path);
		$result['result'] = self::RESULT_OK;
		return $result;
	}

	/**
	 * @api {get} /list-extension.php /Extension/list
	 * @apiDescription Permet de lister les extensions ainsi que toutes les informations (connecteur, flux, ...)
	 * @apiGroup Extension
	 * @apiVersion 1.0.0
	 * @apiSuccess {Object[]} extension tableau contenant la liste des extensions avec l'id de l'extension comme clé et les informations sur l'extension
	 */
	public function listAction(){
		$result['result'] = $this->systemControler->extensionList();
		return $result;
	}

	/**
	 * @api {get} /delete-extension.php /Extension/delete
	 * @apiDescription Supression  d'une extension
	 * @apiGroup Extension
	 * @apiVersion 1.0.0
	 * @apiParam {string} id_extension Identifiant de l'extension à modifier, 0 ou rien pour créer une extension
	 * @apiSuccess {string} result ok si tout est ok
	 */
	public function deleteAction(){
		$id_extension = $this->getFromRequest('id_extension');
		$this->systemControler->extensionDelete($id_extension);
		$result['result'] = self::RESULT_OK;
		return $result;
	}

}
