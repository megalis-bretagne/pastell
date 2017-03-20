<?php

class ExtensionAPIController extends BaseAPIController {

	private $extensions;

	private $extensionSQL;

	public function __construct(
		Extensions $extensions,
		ExtensionSQL $extensionSQL
	) {
		$this->extensions = $extensions;
		$this->extensionSQL = $extensionSQL;
	}

	/**
	 * @api {get} /Extension/list /Extension/list
	 * @apiDescription Permet de lister les extensions ainsi que toutes les informations (connecteur, flux, ...) (was: /list-extension.php)
	 * @apiGroup Extension
	 * @apiVersion 1.0.0
	 * @apiSuccess {Object[]} extension tableau contenant la liste des extensions avec l'id de l'extension comme clé et les informations sur l'extension
	 */
	public function listAction(){
		$this->verifDroit(0,"system:lecture");
		$result['result'] = $this->extensions->getAll();
		return $result;
	}


	/**
	 * @api {get} /Extension/edit /Extension/edit
	 * @apiDescription Ajout ou modification du chemin d'une extension (was: /edit-extension.php)
	 * @apiGroup Extension
	 * @apiVersion 1.0.0
	 * @apiParam {string} id_extension Identifiant de l'extension à modifier, 0 ou rien pour créer une extension
	 * @apiParam {string} path Emplacement de l'extension sur le système de fichier
	 * @apiSuccess {string} result ok si tout est ok
	 */
	public function editAction(){
		$this->verifDroit(0,"system:edition");
		
		$id_extension = $this->getFromRequest('id_extension');
		$path = $this->getFromRequest('path');

		if (! file_exists($path)){
			throw new Exception("Le chemin « $path » n'existe pas sur le système de fichier");
		}
		if ($id_extension){
			$info_extension = $this->extensionSQL->getInfo($id_extension);
			if (!$info_extension) {
				throw new Exception("L'extension #{$id_extension} est introuvable");
			}
		}

		$detail_extension = $this->extensions->getInfo($id_extension, $path);
		$extension_list = $this->extensions->getAll();

		foreach($extension_list as $id_e => $extension) {
			if (($extension['id'] == $detail_extension['id']) && !($extension['id_e'] == $detail_extension['id_e'])) {
				throw new Exception("L'extension #{$detail_extension['id']} est déja présente");
			}
		}
		$this->extensionSQL->edit($id_extension,$path); // ajout ou modification


		$result['detail_extension'] = $detail_extension;
		$result['result'] = self::RESULT_OK;
		return $result;
	}


	/**
	 * @api {get} /Extension/delete /Extension/delete
	 * @apiDescription Supression  d'une extension (was :  /delete-extension.php)
	 * @apiGroup Extension
	 * @apiVersion 1.0.0
	 * @apiParam {string} id_extension Identifiant de l'extension à modifier, 0 ou rien pour créer une extension
	 * @apiSuccess {string} result ok si tout est ok
	 */
	public function deleteAction(){
		$this->verifDroit(0,"system:edition");
		$id_extension = $this->getFromRequest('id_extension');
		if (! $id_extension || ! $this->extensionSQL->getInfo($id_extension)){
			throw new Exception("Extension #$id_extension non trouvée");
		}
		$this->extensionSQL->delete($id_extension);
		$result['result'] = self::RESULT_OK;
		return $result;
	}

}
