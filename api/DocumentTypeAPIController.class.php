<?php

class DocumentTypeAPIController extends BaseAPIController {

	/** @var  DocumentTypeFactory */
	private $documentTypeFactory;

	public function __construct(DocumentTypeFactory $documentTypeFactory) {
		$this->documentTypeFactory = $documentTypeFactory;
	}

	/**
	 * @api {get} /DocumentType/list /DocumentType/list
	 * @apiDescription Liste l'ensemble des types de flux disponibles pour l'utilisateur connecté. (was: /document-type.php)
	 * @apiGroup DocumentType
	 * @apiVersion 1.0.0
	 * @apiSuccess {Object} document_type Nom symbolique du type de flux (exemple : actes-generique, helios-generique)
	 * @apiSuccess {String} document_type.type Le groupe de type de flux (exemple: flux généraux)
	 * @apiSuccess {String} document_type.nom Libellé du type de flux (exemple : Actes, Message du centre de gestion)
	 *
	 *
	 * @apiSuccessExample {json} Success-Reponse:
	 * 	{
	 * 		"openid-authentification":
	 * 			{
	 * 				"type":"Flux G\u00e9n\u00e9raux",
	 * 				"nom":"Authentification OpenID"
	 * 			},
	 * 		"mailsec":
	 * 			{
	 * 				"type":"Flux G\u00e9n\u00e9raux",
	 * 				"nom":"Mail s\u00e9curis\u00e9"
	 * 			}
	 * 	}
	 *
	 *
	 */
	public function listAction(){
		$allDocType = $this->documentTypeFactory->getAllType();
		$allType = array();
		foreach($allDocType as $type_flux => $les_flux){
			foreach($les_flux as $nom => $affichage) {
				if ($this->getRoleUtilisateur()->hasOneDroit($this->getUtilisateurId(),$nom.":lecture")){
					$allType[$nom]  = array('type'=>$type_flux,'nom'=>$affichage);
				}
			}
		}
		return $allType;
	}

	/**
	 * @api {get}  /DocumentType/info /document-type-info.php
	 * @apiDescription 	Liste l'ensemble des champs d'un type document
	 * 					ainsi que les informations sur chaque champs (type de champs, valeur par défaut,
	 * 					script de choix, ...) (was: /document-type-info.php)
	 * @apiGroup DocumentType
	 * @apiVersion 1.0.0
	 * @apiParam {string} type Un des type retourné par la fonction document-type.php
	 * @apiSuccessExample {json} Success-Reponse:
	 * 		{
	 * 			"id_u":
	 * 				{
	 * 					"no-show":true,
	 * 					"name":"id_u"
	 * 				},
	 * 			"login":
	 * 				{
	 * 					"title":true,
	 * 					"name":"login"
	 * 				}
	 * 		}
	 */
	public function infoAction(){
		$type = $this->getFromRequest('type');
		if ( !  $this->getRoleUtilisateur()->hasOneDroit($this->getUtilisateurId(),"$type:lecture")) {
			throw new Exception("Acces interdit type=$type,id_u={$this->getUtilisateurId()}");
		}

		$documentType = $this->documentTypeFactory->getFluxDocumentType($type);
		$formulaire = $documentType->getFormulaire();
		$result = array();
		/**
		 * @var Field $fields
		 */
		foreach($formulaire->getAllFields() as $key => $fields){
			$result[$key] = $fields->getAllProperties();
		}
		return $result;
	}

	/**
	 * @api {get} /DocumentType/actionList /DocumentType/actionList
	 * @apiDescription 	ramène la liste des statuts/actions possibles sur ce type de document
	 * 					ainsi que des infos relatives à ce type de document (was: /document-type-action.php)
	 * @apiGroup DocumentType
	 * @apiVersion 1.0.0
	 * @apiParam {string} type Un des type retourné par la fonction document-type.php
	 */
	public function actionListAction(){
		$type = $this->getFromRequest('type');
		if ( !  $this->getRoleUtilisateur()->hasOneDroit($this->getUtilisateurId(),"$type:lecture")) {
			throw new Exception("Acces interdit type=$type,id_u={$this->getUtilisateurId()}");
		}

		$documentType = $this->documentTypeFactory->getFluxDocumentType($type);
		return $documentType->getTabAction();
	}

}