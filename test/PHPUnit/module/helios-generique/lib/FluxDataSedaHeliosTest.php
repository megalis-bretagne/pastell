<?php

class FluxDataSedaHeliosTest extends PastellTestCase
{
    public function testAccentuatedLibelleCodBud()
    {
        $donneesFormulaire = $this->getDonneesFormulaireFactory()->getNonPersistingDonneesFormulaire();
        $donneesFormulaire->addFileFromCopy(
            'fichier_pes',
            'pes_aller.xml',
            __DIR__ . '/../fixtures/HELIOS_SIMU_ALR2_LibelleColBud_avec_accent.xml'
        );

        $fluxDataSedaHelios = new FluxDataSedaHelios($donneesFormulaire);

        $this->assertSame('Test é(-è_çà)=', $fluxDataSedaHelios->get_LibelleColBud());
    }
}
