<?php

class HeliosGeneriqueFichierPESChange extends ActionExecutor
{
    private $heliosGeneriquePESAller;

    public function __construct(ObjectInstancier $objectInstancier, PESAllerFile $heliosGeneriquePESAller)
    {
        parent::__construct($objectInstancier);
        $this->heliosGeneriquePESAller = $heliosGeneriquePESAller;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function go()
    {
        $info = $this->heliosGeneriquePESAller->getAllInfo($this->getDonneesFormulaire()->getFilePath('fichier_pes'));

        $info_to_retrieve = array (
            PESAllerFile::ID_COLL => 'id_coll',
            PESAllerFile::DTE_STR => 'dte_str',
            PESAllerFile::COD_BUD => 'cod_bud',
            PESAllerFile::EXERCICE => 'exercice',
            PESAllerFile::ID_BORD => 'id_bordereau',
            PESAllerFile::ID_PJ => 'id_pj',
            PESAllerFile::ID_PCE => 'id_pce',
            PESAllerFile::ID_NATURE => 'id_nature',
            PESAllerFile::ID_FONCTION => 'id_fonction',
        );

        if (! $this->getDonneesFormulaire()->get('objet')) {
            $this->getDonneesFormulaire()->setData('objet', $info[PESAllerFile::NOM_FIC]);
            $this->getDocument()->setTitre($this->id_d, $info[PESAllerFile::NOM_FIC]);
        }

        foreach ($info_to_retrieve as $pes_element_name => $pastell_element_name) {
            $this->getDonneesFormulaire()->setData($pastell_element_name, $info[$pes_element_name]);
        }
        $this->getDonneesFormulaire()->setData('etat_ack', 0);

        return true;
    }
}
