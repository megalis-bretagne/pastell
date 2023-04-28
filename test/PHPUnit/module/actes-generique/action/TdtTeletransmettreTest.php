<?php

class TdtTeletransmettreTest extends PastellTestCase
{
    /**
     * @throws Exception
     */
    public function testCasNominal()
    {
        $connecteur_info = $this->createConnector('s2low', "s2low");
        $connecteurDonneesFormulaire = $this
            ->getDonneesFormulaireFactory()
            ->getConnecteurEntiteFormulaire($connecteur_info['id_ce']);
        $connecteurDonneesFormulaire->setTabData([
            'url' => 'https://www/',
            'authentication_for_teletransmisson' => 'On'
        ]);

        $this->associateFluxWithConnector($connecteur_info['id_ce'], "actes-generique", "TdT");

        $document_info = $this->createDocument('actes-generique');


        $id_d = $document_info['id_d'];

        ob_start();
        $this->triggerActionOnDocument($id_d, "teletransmission-tdt");
        $contents = ob_get_contents();
        ob_end_clean();
        $this->assertSame(
            'Location: https://www//modules/actes/actes_transac_post_confirm_api.php?id=&url_return=' .
            urlencode($this->getSiteBase()) .
            "%2FDocument%2Faction%3Fid_d%3D{$id_d}%26id_e%3D1%26action%3Dreturn-teletransmission-tdt%26error%3D%25%25ERROR%25%25%26message%3D%25%25MESSAGE%25%25\n",
            $contents
        );
    }
}
