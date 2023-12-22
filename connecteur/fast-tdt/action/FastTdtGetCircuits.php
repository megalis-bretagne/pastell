<?php

class FastTdtGetCircuits extends DictionnaryChoice
{
    public function getElementId(): string
    {
        return 'circuit';
    }

    public function getElementName(): string
    {
        return 'circuit_name';
    }

    public function getTitle(): string
    {
        return 'Sélectionner un circuit';
    }

    /**
     * @throws Exception
     */
    public function displayAPI(): string|array
    {
        /** @var FastTdt $connecteur */
        $connecteur = $this->getMyConnecteur();
        return array_column(
            $connecteur->getHeliosClient()->getCircuits($connecteur->getSiren()),
            'circuitName',
            'circuitId'
        );
    }
}
