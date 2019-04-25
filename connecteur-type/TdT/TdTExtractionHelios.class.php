<?php

require_once (__DIR__."/../../module/helios-generique/lib/HeliosGeneriquePESAller.class.php");

class TdTExtractionHelios extends ConnecteurTypeActionExecutor{

	public function go(){
		$fichier_pes_element = $this->getMappingValue('fichier_pes');

		$info = $this->objectInstancier->getInstance(HeliosGeneriquePESAller::class)->getAllInfo($this->getDonneesFormulaire()->getFilePath($fichier_pes_element));

		$info_to_retrieve = array (
			HeliosGeneriquePESAller::ID_COLL => 'id_coll',
			HeliosGeneriquePESAller::DTE_STR => 'dte_str',
			HeliosGeneriquePESAller::COD_BUD => 'cod_bud',
			HeliosGeneriquePESAller::EXERCICE => 'exercice',
			HeliosGeneriquePESAller::ID_BORD => 'id_bordereau',
			HeliosGeneriquePESAller::ID_PJ => 'id_pj',
			HeliosGeneriquePESAller::ID_PCE => 'id_pce',
			HeliosGeneriquePESAller::ID_NATURE => 'id_nature',
			HeliosGeneriquePESAller::ID_FONCTION => 'id_fonction',
		);

		foreach($info_to_retrieve as $pes_element_name => $pastell_element_name){
			$this->getDonneesFormulaire()->setData($this->getMappingValue($pastell_element_name),$info[$pes_element_name]);
		}
		$this->getDonneesFormulaire()->setData($this->getMappingValue('pes_etat_ack'),0);
		$this->getDonneesFormulaire()->setData($this->getMappingValue('pes_information_pes_aller'),true);


		$this->addActionOK("Les données ont été extraites du fichier PES ALLER");
		return true;
	}

}