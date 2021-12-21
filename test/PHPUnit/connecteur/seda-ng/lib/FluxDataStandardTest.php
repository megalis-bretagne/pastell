<?php

class FluxDataStandardTest extends PastellTestCase
{
    /**
     * @throws UnrecoverableException
     */
    public function testWhenElementDoesNotExist()
    {
        $donneesFormulaire = $this->getDonneesFormulaireFactory()->getNonPersistingDonneesFormulaire();
        $fluxDataStandard = new FluxDataStandard($donneesFormulaire);
        $this->expectException(UnrecoverableException::class);
        $this->expectExceptionMessage(
            "Impossible de trouver le fichier correspondant à l'élément « not_existing ». Merci de vérifier le profil d'archivage annoté."
        );
        $fluxDataStandard->getFileSHA256('not_existing');
    }
}
