<?php

require_once PASTELL_PATH . "/connecteur-type/signature/SignatureRecuperation.class.php";
require_once __DIR__ . "/../lib/AttrFactureCPP.class.php";
require_once PASTELL_PATH . "/lib/Array2XML.class.php";

class FactureCPPIParapheurRecup extends SignatureRecuperation
{
    public function go()
    {

        // Dans le cas de l'héritage on fait le mapping ici (il n'est pas dans le definition.yml)
        $this->setMapping([
            "document" => "fichier_facture_pdf",
            "titre" => "id_facture_cpp",
            "autre_document_attache" => "facture_pj_02",
            "has_signature" => "has_visa"
        ]);

        $result_parapheur = parent::go();

        if ($result_parapheur) {
            $donneesFormulaire = $this->getDonneesFormulaire();
            if ($this->getActionName() == self::ACTION_NAME_RECU) {
                $donneesFormulaire->setData(AttrFactureCPP::ATTR_STATUT_CIBLE_LISTE, PortailFactureConnecteur::STATUT_SERVICE_FAIT);
                $codeService = $this->getMetaDonnee("CodeService");
                $donneesFormulaire->setData(AttrFactureCPP::ATTR_SERVICE_DESTINATAIRE_CODE, $codeService);
            }
            if ($this->getActionName() == self::ACTION_NAME_REJET) {
                $statutCible = $this->getMetaDonnee("chorusproStatutRejet");
                if (!$statutCible) {
                    $statutCible = "REJETEE";
                }
                $donneesFormulaire->setData(AttrFactureCPP::ATTR_STATUT_CIBLE_LISTE, $statutCible);
                $lastState = $donneesFormulaire->get('parapheur_last_message');
                $donneesFormulaire->setData(AttrFactureCPP::ATTR_MOTIF_MAJ, $lastState);
            }
        }

        return $result_parapheur;
    }
}
