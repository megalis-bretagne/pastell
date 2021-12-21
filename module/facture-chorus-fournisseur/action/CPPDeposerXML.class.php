<?php

class CPPDeposerXML extends ActionExecutor
{
    public function go()
    {
        /** @var CPP $portailFature */
        $portailFature = $this->getConnecteur("PortailFacture");
        $filename = $this->getDonneesFormulaire()->getFileName('fichier_facture_pdf');
        $filecontent = $this->getDonneesFormulaire()->getFileContent('fichier_facture_pdf');
        $syntaxe_flux = $this->getDonneesFormulaire()->get('syntaxe_flux_in');
        $result = $portailFature->deposerXML($filename, $filecontent, $syntaxe_flux);

        if (empty($result['numeroFluxDepot'])) {
            $message = "{$result['libelle']} - Code retour : {$result['codeRetour']}";
            throw new Exception($message);
        }

        $map = array (
            "numeroFluxDepot" => 'numero_flux_depot',
            "syntaxeFlux" => 'syntaxe_flux',
            "dateDepot" => 'date_depot'
        );
        foreach ($map as $item_chorus => $item_pastell) {
            $this->getDonneesFormulaire()->setData($item_pastell, $result[$item_chorus]);
        }

        $this->getDonneesFormulaire()->setData('has_donnees_chorus_pro_xml', true);
        $this->addActionOK(
            "La facture a été déposée sur Chorus Pro " .
            "avec l'identifiant {$result['numeroFluxDepot']}"
        );

        return true;
    }
}
