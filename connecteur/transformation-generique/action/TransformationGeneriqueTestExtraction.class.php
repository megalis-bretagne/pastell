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

        $result =  $connecteur->testTransform();

        $this->setLastMessage("Résultat de l'extraction : " . $result);
        return true;
    }
}
