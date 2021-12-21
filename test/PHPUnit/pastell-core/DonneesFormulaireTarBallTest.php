<?php

class DonneesFormulaireTarBallTest extends PastellTestCase
{
    /**
     * @return DonneesFormulaire
     */
    private function getDonneesFormulaire()
    {
        return $this->getObjectInstancier()->DonneesFormulaireFactory->get('toto', 'test');
    }

    public function testExtract()
    {
        $donneesFormulaire = $this->getDonneesFormulaire();
        $file_path = __DIR__ . "/../fixtures/tar_ball/courrier_simple.tar.gz";
        $donneesFormulaire->addFileFromCopy('fichier_in', basename($file_path), $file_path);

        $donneesFormulaireTarBall = new DonneesFormulaireTarBall(new TmpFolder());
        $donneesFormulaireTarBall->extract($donneesFormulaire, 'fichier_in', 'fichier_out');

        $this->assertEquals($donneesFormulaire->getFileName('fichier_out', 0), "001-862614864-20150805-20150805B-AI-2-1_0.xml");
        $this->assertCount(3, $donneesFormulaire->get('fichier_out'));
    }
}
