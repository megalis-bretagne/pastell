<?php

require_once __DIR__."/lib/ActesTypePJData.class.php";
require_once __DIR__."/lib/ActesTypePJ.class.php";

/**
 *
 * @deprecated PA 3.0.0
 * Il faut utiliser la fonction de l'API externalData et ne pas modifié directement type_acte et type_pj
 *
 *
 */
class TdtTypologieChangeByApi extends ConnecteurTypeActionExecutor {

	/**
	 * @return bool
	 * @throws UnrecoverableException
	 * @throws Exception
	 */
	public function go(){

		$info = $this->displayAPI();
		$connecteur_type_action = $this->getMappingList();

		$type_acte = $this->getDonneesFormulaire()->get('type_acte');
		$type_pj = json_decode($this->getDonneesFormulaire()->get('type_pj',"[]"));


		$result[] =  ['filename' => $info['pieces'][0], "typologie"=>$info['actes_type_pj_list'][$type_acte]];

		foreach($type_pj as $i => $type){
			$result[] = ['filename' => $info['pieces'][$i+1], "typologie"=>$info['actes_type_pj_list'][$type]];
		}

		$this->getDonneesFormulaire()->setData(
			$connecteur_type_action['type_piece']??'type_piece',
			(count($type_pj)+1) . " fichier(s) typé(s)"
		);

		$this->getDonneesFormulaire()->addFileFromData(
			$connecteur_type_action['type_piece_fichier']??'type_piece_fichier',
			'type_piece.json',
			json_encode($result)
		);

		return true;

	}

	/**
	 * @throws Exception
	 * @throws UnrecoverableException
	 */
	public function displayAPI(){
		$result = array();

		$connecteur_type_action = $this->getMappingList();

		$actesTypePJData = new ActesTypePJData();

		$configTdt = $this->getConnecteurConfigByType(TdtConnecteur::FAMILLE_CONNECTEUR);
		$actesTypePJData->classification_file_path = $configTdt->getFilePath($connecteur_type_action['classification_file']??'classification_file');

		$actesTypePJData->acte_nature = $this->getDonneesFormulaire()->get($connecteur_type_action['acte_nature']??'acte_nature');

		$actesTypePJ = $this->objectInstancier->getInstance(ActesTypePJ::class);

		$result['actes_type_pj_list'] = $actesTypePJ->getTypePJListe($actesTypePJData);
		if (! $result['actes_type_pj_list']){
			throw new UnrecoverableException("Aucun type de pièce ne correspond pour la nature et la classification selectionnée");
		}

		$result['pieces'] = $this->getAllPieces();
		return $result;
	}

	/**
	 * @return array|string
	 * @throws UnrecoverableException
	 */
	private function getAllPieces(){

		$connecteur_type_action = $this->getMappingList();


		$pieces_list = $this->getDonneesFormulaire()->get($connecteur_type_action['arrete']??'arrete');
		if (! $pieces_list){
			throw new UnrecoverableException("La pièce principale n'est pas présente");
		}
		if($this->getDonneesFormulaire()->get($connecteur_type_action['autre_document_attache']??'autre_document_attache')) {
			$pieces_list = array_merge($pieces_list, $this->getDonneesFormulaire()->get($connecteur_type_action['autre_document_attache']??'autre_document_attache'));
		}
		return $pieces_list;
	}

	private function getMappingList(){
		return $this->getDocumentType()->getAction()->getProperties($this->action,'connecteur-type-mapping');
	}


}