<?php

require_once __DIR__."/lib/ActesTypePJData.class.php";
require_once __DIR__."/lib/ActesTypePJ.class.php";

/**
 *
 * @deprecated PA 3.0.0
 * Il faut utiliser la fonction de l'API externalData et ne pas modifier directement type_acte et type_pj
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

		$type_acte_element = $this->getMappingValue('type_acte');
		$type_pj_element = $this->getMappingValue('type_pj');
		$type_piece_element = $this->getMappingValue('type_piece');
		$type_piece_fichier_element = $this->getMappingValue('type_piece_fichier');

		$info = $this->displayAPI();

		$type_acte = $this->getDonneesFormulaire()->get($type_acte_element);
		$type_pj = json_decode($this->getDonneesFormulaire()->get($type_pj_element,"[]"));


		$result[] =  ['filename' => $info['pieces'][0], "typologie"=>$info['actes_type_pj_list'][$type_acte]];

		foreach($type_pj as $i => $type){
			$result[] = ['filename' => $info['pieces'][$i+1], "typologie"=>$info['actes_type_pj_list'][$type]];
		}

		$this->getDonneesFormulaire()->setData(
			$type_piece_element,
			(count($type_pj)+1) . " fichier(s) typé(s)"
		);

		$this->getDonneesFormulaire()->addFileFromData(
			$type_piece_fichier_element,
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

		$classification_file_element = $this->getMappingValue('classification_file');
		$acte_nature = $this->getMappingValue('acte_nature');

		$actesTypePJData = new ActesTypePJData();

		$configTdt = $this->getConnecteurConfigByType(TdtConnecteur::FAMILLE_CONNECTEUR);
		$actesTypePJData->classification_file_path = $configTdt->getFilePath($classification_file_element);

		$actesTypePJData->acte_nature = $this->getDonneesFormulaire()->get($acte_nature);

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

		$arrete_element = $this->getMappingValue('arrete');
		$autre_document_attache = $this->getMappingValue('autre_document_attache');

		$pieces_list = $this->getDonneesFormulaire()->get($arrete_element);
		if (! $pieces_list){
			throw new UnrecoverableException("La pièce principale n'est pas présente");
		}
		if($this->getDonneesFormulaire()->get($autre_document_attache)) {
			$pieces_list = array_merge($pieces_list, $this->getDonneesFormulaire()->get($autre_document_attache));
		}
		return $pieces_list;
	}



}