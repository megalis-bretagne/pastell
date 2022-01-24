<?php

class FakeSEDATest extends PastellTestCase
{
    /**
     * @throws Exception
     */
    public function testCoverAll()
    {
        $fakeSEDA = new FakeSEDA();

        $this->assertStringEqualsFile(
            PASTELL_PATH . "/connecteur/FakeSEDA/fixtures/bordereau.xml",
            $fakeSEDA->getBordereau([])
        );

        $donnesFormulaire = $this->getDonneesFormulaireFactory()->getNonPersistingDonneesFormulaire();

        $this->assertStringEqualsFile(
            PASTELL_PATH . "/connecteur/FakeSEDA/fixtures/bordereau.xml",
            $fakeSEDA->getBordereauNG(new FluxDataSedaDefault($donnesFormulaire))
        );

        $this->assertTrue($fakeSEDA->validateBordereau(""));
        $this->assertEmpty($fakeSEDA->getLastValidationError());

        $fakeSEDA->setConnecteurConfig($donnesFormulaire);


        $tmpFolder = new TmpFolder();
        $tmp_folder = $tmpFolder->create();

        $fakeSEDA->generateArchive(new FluxDataSedaDefault($donnesFormulaire), "$tmp_folder/toto");
        $this->assertFileExists("$tmp_folder/toto");
        $tmpFolder->delete($tmp_folder);
    }
}
