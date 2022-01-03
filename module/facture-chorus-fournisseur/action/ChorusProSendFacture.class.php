<?php

class ChorusProSendFacture extends ActionExecutor
{
    public function go()
    {

        /** @var CPP $portailFature */
        $portailFature = $this->getConnecteur("PortailFacture");

        $result  = $portailFature->soumettreFacture($this->getDonneesFormulaire());
        if (empty($result['identifiantFactureCPP'])) {
            $message = "{$result['libelle']} - Code retour : {$result['codeRetour']}";
            throw new Exception($message);
        }
        $map = array (
            "identifiantFactureCPP" => 'identifiant_facture_cpp',
            "statutFacture" => 'statut_facture',
            "dateDepot" => 'date_depot'
        );
        foreach ($map as $item_chorus => $item_pastell) {
            $this->getDonneesFormulaire()->setData($item_pastell, $result[$item_chorus]);
        }

        $file_name = $this->getDonneesFormulaire()->getFileName('fichier_facture_pdf', 0);
        $file_path = $this->getDonneesFormulaire()->getFilePath('fichier_facture_pdf', 0);
        $this->getDonneesFormulaire()->addFileFromCopy('fichier_original', $file_name, $file_path, 0);

        $this->getDonneesFormulaire()->setData('has_donnees_chorus_pro', true);
        $this->addActionOK(
            "La facture {$result['numeroFacture']} a été déposée sur Chorus Pro " .
            "avec l'identifiant {$result['identifiantFactureCPP']}"
        );

        return true;
    }
}
