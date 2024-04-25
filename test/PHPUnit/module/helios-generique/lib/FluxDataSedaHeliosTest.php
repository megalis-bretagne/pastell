<?php

declare(strict_types=1);

class FluxDataSedaHeliosTest extends PastellTestCase
{
    /**
     * @throws DonneesFormulaireException
     */
    public function testAccentuatedLibelleCodBud(): void
    {
        $donneesFormulaire = $this->getDonneesFormulaireFactory()->getNonPersistingDonneesFormulaire();
        $donneesFormulaire->addFileFromCopy(
            'fichier_pes',
            'pes_aller.xml',
            __DIR__ . '/../fixtures/HELIOS_SIMU_ALR2_LibelleColBud_avec_accent.xml'
        );

        $fluxDataSedaHelios = new FluxDataSedaHelios($donneesFormulaire);

        static::assertSame('Test é(-è_çà)=', $fluxDataSedaHelios->get_LibelleColBud());
    }

    /**
     * @throws DonneesFormulaireException
     */
    public function testBlocPieceWithoutInfoPce(): void
    {
        $donneesFormulaire = $this->getDonneesFormulaireFactory()->getNonPersistingDonneesFormulaire();
        $donneesFormulaire->addFileFromCopy(
            'fichier_pes',
            'pes_aller.xml',
            __DIR__ . '/../fixtures/HELIOS_SIMU_ALR2_BlocPiece-without-InfoPce.xml'
        );

        $fluxDataSedaHelios = new FluxDataSedaHelios($donneesFormulaire);

        static::assertSame(
            [
                'IdBord 1234567',
                'IdPce 832',
            ],
            $fluxDataSedaHelios->get_IdBord_IdPce()
        );
    }
}
