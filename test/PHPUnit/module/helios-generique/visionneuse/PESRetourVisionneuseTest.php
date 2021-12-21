<?php

class PESRetourVisionneuseTest extends PastellTestCase
{
    public function testVisionneuse()
    {
        $id_d = $this->createDocument("helios-generique")['id_d'];

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);

        $donneesFormulaire->addFileFromCopy(
            'fichier_reponse',
            'pes_acquit.xml',
            __DIR__ . "/../fixtures/pes_acquit_no_ack.xml"
        );

        $visionneuseFactory = $this->getObjectInstancier()->getInstance(VisionneuseFactory::class);

        ob_start();
        $visionneuseFactory->display($id_d, 'fichier_reponse');
        $result = ob_get_contents();
        ob_end_clean();

        $this->assertRegExp("#Rapport acquittement#", $result);
        $expected_error_line = 'Sur pièce n° 514                                , ligne n° 1                                , ERREUR_AUTRE : 1963 - Domiciliation erron&eacute;e';
        $this->assertRegExp("#$expected_error_line#", $result);
    }
}
