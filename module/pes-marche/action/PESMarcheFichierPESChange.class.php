<?php

require_once(__DIR__ . "/../lib/PESMarcheInfo.class.php");

class PESMarcheFichierPESChange extends ActionExecutor
{
    private $PESMarcheInfo;

    public function __construct(ObjectInstancier $objectInstancier, PESMarcheInfo $PESMarcheInfo)
    {
        parent::__construct($objectInstancier);
        $this->PESMarcheInfo = $PESMarcheInfo;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function go()
    {
        $info = $this->PESMarcheInfo->getAllInfo($this->getDonneesFormulaire()->getFilePath('fichier_pes'));

        $info_to_retrieve = array (
            PESMarcheInfo::ID_COLL => 'id_coll',
            PESMarcheInfo::DTE_STR => 'dte_str',
            PESMarcheInfo::COD_BUD => 'cod_bud',
            PESMarcheInfo::ID_CONTRAT => 'id_contrat',
            PESMarcheInfo::ID_PJ => 'id_pj',
        );

        if (! $this->getDonneesFormulaire()->get('objet')) {
            $this->getDonneesFormulaire()->setData('objet', $info[PESMarcheInfo::NOM_FIC]);
            $this->getDocument()->setTitre($this->id_d, $info[PESMarcheInfo::NOM_FIC]);
        }

        foreach ($info_to_retrieve as $pes_element_name => $pastell_element_name) {
            $this->getDonneesFormulaire()->setData($pastell_element_name, $info[$pes_element_name]);
        }
        $this->getDonneesFormulaire()->setData('etat_ack', 0);

        $this->getDonneesFormulaire()->setData('has_information_pes_aller', 1);

        $this->getAllPJ($this->getDonneesFormulaire());

        $this->addActionOK("Les données du PES Aller ont été extraites");
        return true;
    }


    /**
     * @param DonneesFormulaire $donneesFormulaire
     * @return bool
     * @throws Exception
     */
    public function getAllPJ(DonneesFormulaire $donneesFormulaire)
    {

        $pj_xml = $this->PESMarcheInfo->getPJXML($this->getDonneesFormulaire()->getFilePath('fichier_pes'));
        $donneesFormulaire->deleteField('piece_justificative');

        if (empty($pj_xml)) {
            return false;
        }

        $file_num = 0;
        foreach ($pj_xml as $pj) {
            $pj_nom = strval($pj->NomPJ['V']);
            $pj_contenu = strval($pj->Contenu->Fichier);
            $file_content = base64_decode($pj_contenu, true);
            $decode_gzip = gzdecode($file_content);
            /** @var DonneesFormulaire $donneesFormulaire */
            $donneesFormulaire->addFileFromData('piece_justificative', $pj_nom, $decode_gzip, $file_num);

            $file_num++;
        }

        return true;
    }
}
