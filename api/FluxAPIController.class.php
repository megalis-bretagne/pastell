<?php

class FluxAPIController extends BaseAPIController {

	/** @var  DocumentTypeFactory */
	private $documentTypeFactory;

	public function __construct(DocumentTypeFactory $documentTypeFactory) {
		$this->documentTypeFactory = $documentTypeFactory;
	}

	public function get(){
		$id_flux = $this->getFromQueryArgs(0);
		if (! $id_flux){
			return $this->listFlux();
		}
		return $this->detail($id_flux);
	}

	public function listFlux(){
		$allDocType = $this->documentTypeFactory->getAllType();
		$allType = array();
		foreach($allDocType as $type_flux => $les_flux){
			foreach($les_flux as $nom => $affichage) {
				if ($this->hasOneDroit($nom.":lecture")){
					$allType[$nom]  = array('type'=>$type_flux,'nom'=>$affichage);
				}
			}
		}
		return $allType;
	}

	public function detail($id_flux){
		if ( !  $this->hasOneDroit("$id_flux:lecture")) {
			throw new NotFoundException("Le flux $id_flux n'existe pas ou vous n'avez pas le droit de lecture dessus");
		}

		$documentType = $this->documentTypeFactory->getFluxDocumentType($id_flux);
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