<?php

class FakeSEDATest extends PastellTestCase
{
    /**
     * @throws Exception
     */
    public function testCoverAll(): void
    {
        $fakeSEDA = new FakeSEDA();
        $donnesFormulaire = $this->getDonneesFormulaireFactory()->getNonPersistingDonneesFormulaire();
        $fluxData = new FluxDataSedaDefault($donnesFormulaire);

        $this->assertStringEqualsFile(
            PASTELL_PATH . '/connecteur/FakeSEDA/fixtures/bordereau.xml',
            $fakeSEDA->getBordereauNG($fluxData)
        );

        $this->assertTrue($fakeSEDA->validateBordereau(""));
        $this->assertEmpty($fakeSEDA->getLastValidationError());

        $fakeSEDA->setConnecteurConfig($donnesFormulaire);

        $tmpFolder = new TmpFolder();
        $tmp_folder = $tmpFolder->create();

        $fakeSEDA->generateArchive($fluxData, "$tmp_folder/toto");
        $this->assertFileExists("$tmp_folder/toto");
        $tmpFolder->delete($tmp_folder);
    }
}
