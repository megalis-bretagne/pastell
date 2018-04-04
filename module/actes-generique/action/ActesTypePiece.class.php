<?php

require_once __DIR__."/../lib/ActesTypePJData.class.php";
require_once __DIR__."/../lib/ActesTypePJ.class.php";

class ActesTypePiece extends ChoiceActionExecutor {


	/**
	 * @throws Exception
	 */
	public function display(){
		$document_info = $this->getDocument()->getInfo($this->id_d);
		$this->{'info'} = $document_info;

		$result = $this->displayAPI();
		$this->{'actes_type_pj_list'} = $result['actes_type_pj_list'];
		$this->{'pieces'} = $result['pieces'];

		$type_pj_selection = [$this->getDonneesFormulaire()->get('type_acte')];

		$type_pj = $this->getDonneesFormulaire()->get('type_pj');
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
		$actesTypePJData = new ActesTypePJData();

		$configTdt = $this->getConnecteurConfigByType(TdtConnecteur::FAMILLE_CONNECTEUR);
		$actesTypePJData->classification_file_path = $configTdt->getFilePath('classification_file');

		$actesTypePJData->acte_nature = $this->getDonneesFormulaire()->get('acte_nature');

		$classification = $this->getDonneesFormulaire()->get('classification');

		$actesTypePJData->actes_matiere1 = $classification[0];
		$actesTypePJData->actes_matiere2 = $classification[2];

		$actesTypePJ = $this->objectInstancier->getInstance(ActesTypePJ::class);

		$result['actes_type_pj_list'] = $actesTypePJ->getTypePJListe($actesTypePJData);
		if (! $result['actes_type_pj_list']){
			throw new Exception("Aucun type de pièce ne correspond pour la nature et la classification selectionnée");
		}

		$result['pieces'] = $this->getDonneesFormulaire()->get('arrete');
		if (! $result['pieces']){
			throw new Exception("La pièce principale n'est pas présente");
		}
		if($this->getDonneesFormulaire()->get('autre_document_attache')) {
			$result['pieces'] = array_merge($result['pieces'], $this->getDonneesFormulaire()->get('autre_document_attache'));
		}
		return $result;
	}

	/**
	 * @return bool
	 * @throws Exception
	 */
	public function go(){
		$info = $this->displayAPI();

		$type_pj = $this->getRecuperateur()->get('type_pj');
		if (! $type_pj){
			throw new Exception("Aucun type_pj fourni");
		}

		foreach($type_pj as $i => $type){
			$result[] = $info['pieces'][$i]. " : ". $info['actes_type_pj_list'][$type];
		}

		$this->getDonneesFormulaire()->setData('type_piece',implode(" ; \n",$result));

		$type_acte  = array_shift($type_pj);
		$this->getDonneesFormulaire()->setData('type_acte',$type_acte);
		$this->getDonneesFormulaire()->setData('type_pj',json_encode($type_pj));
		return true;
	}

}