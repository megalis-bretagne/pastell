<?php

require_once (__DIR__."/../lib/HeliosGeneriquePESAller.class.php");

class HeliosGeneriqueFichierPESChange extends ActionExecutor{

    private $heliosGeneriquePESAller;

    public function __construct(ObjectInstancier $objectInstancier, HeliosGeneriquePESAller $heliosGeneriquePESAller) {
        parent::__construct($objectInstancier);
        $this->heliosGeneriquePESAller = $heliosGeneriquePESAller;
    }

    public function go(){
        $info = $this->heliosGeneriquePESAller->getAllInfo($this->getDonneesFormulaire()->getFilePath('fichier_pes'));

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

        if (! $this->getDonneesFormulaire()->get('objet')){
			$this->getDonneesFormulaire()->setData('objet',$info[HeliosGeneriquePESAller::NOM_FIC]);
			$this->getDocument()->setTitre($this->id_d,$info[HeliosGeneriquePESAller::NOM_FIC]);
		}

        foreach($info_to_retrieve as $pes_element_name => $pastell_element_name){
            $this->getDonneesFormulaire()->setData($pastell_element_name,$info[$pes_element_name]);
        }
        $this->getDonneesFormulaire()->setData('etat_ack',0);

        return true;
    }

}