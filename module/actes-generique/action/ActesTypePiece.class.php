<?php

require_once __DIR__."/../lib/ActesTypePJData.class.php";
require_once __DIR__."/../lib/ActesTypePJ.class.php";

class ActesTypePiece extends ChoiceActionExecutor {

	/**
	 *
	 * arrete: arrete
	 * autre_document_attache: autre_document_attache
	 * type_acte: type_acte
	 * type_pj: type_pj
	 * type_piece: type_piece
	 *
	 * acte_nature: acte_nature
	 * classification_file: classification_file
	 *
	 */


	/**
	 * @throws Exception
	 */
	public function display(){
		$connecteur_type_action = $this->getMappingList();

		$document_info = $this->getDocument()->getInfo($this->id_d);
		$this->{'info'} = $document_info;

		$result = $this->displayAPI();
		$this->{'actes_type_pj_list'} = $result['actes_type_pj_list'];
		$this->{'pieces'} = $result['pieces'];

		$type_pj_selection = [$this->getDonneesFormulaire()->get($connecteur_type_action['type_acte']??'type_acte')];

		$type_pj = $this->getDonneesFormulaire()->get($connecteur_type_action['type_pj']??'type_pj');
		if ($type_pj) {
			$type_pj_selection = array_merge($type_pj_selection, json_decode($type_pj));
		}
		$type_pj_selection = array_pad($type_pj_selection,count($this->{'pieces'}),0);

		$this->{'type_pj_selection'} = $type_pj_selection;

		$this->renderPage("Choix des types de pièces",__DIR__."/../template/ActesTypePiece.php");
	}

	/**
	 * @throws Exception
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
			throw new Exception("Aucun type de pièce ne correspond pour la nature et la classification selectionnée");
		}

		$result['pieces'] = $this->getAllPieces();
		return $result;
	}

	private function getAllPieces(){

		$connecteur_type_action = $this->getMappingList();


		$pieces_list = $this->getDonneesFormulaire()->get($connecteur_type_action['arrete']??'arrete');
		if (! $pieces_list){
			throw new Exception("La pièce principale n'est pas présente");
		}
		if($this->getDonneesFormulaire()->get($connecteur_type_action['autre_document_attache']??'autre_document_attache')) {
			$pieces_list = array_merge($pieces_list, $this->getDonneesFormulaire()->get($connecteur_type_action['autre_document_attache']??'autre_document_attache'));
		}
		return $pieces_list;
	}

	private function getMappingList(){
		return $this->getDocumentType()->getAction()->getProperties($this->action,'connecteur-type-mapping');
	}

	/**
	 * @return bool
	 * @throws Exception
	 */
	public function go(){
		$info = $this->displayAPI();

		$connecteur_type_action = $this->getMappingList();

		$type_pj = $this->getRecuperateur()->get('type_pj');
		if (! $type_pj){
			throw new Exception("Aucun type_pj fourni");
		}

		foreach($type_pj as $i => $type){
			$result[] = $info['pieces'][$i]. " : ". $info['actes_type_pj_list'][$type];
		}

		$this->getDonneesFormulaire()->setData($connecteur_type_action['type_piece']??'type_piece',implode(" ; \n",$result));

		$type_acte  = array_shift($type_pj);
		$this->getDonneesFormulaire()->setData($connecteur_type_action['type_acte']??'type_acte',$type_acte);
		$this->getDonneesFormulaire()->setData($connecteur_type_action['type_pj']??'type_pj',json_encode($type_pj));
		return true;
	}

}