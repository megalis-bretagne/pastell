<?php

class TransformationGeneriqueTestExtraction extends ActionExecutor
{
    /**
     * @return bool
     * @throws UnrecoverableException
     */
    public function go(): bool
    {
        /** @var TransformationGenerique $connecteur */
        $connecteur = $this->getMyConnecteur();
        $donneesFormulaire = $this->getDonneesFormulaireFactory()->getNonPersistingDonneesFormulaire();

        $result =  $connecteur->testTransform($donneesFormulaire);

        $this->setLastMessage("Résultat de l'extraction : " . $result);
        return true;
    }
}
