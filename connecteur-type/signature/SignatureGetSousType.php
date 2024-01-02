<?php

class SignatureGetSousType extends DictionnaryChoice
{
    /**
     * @throws Exception
     */
    public function displayAPI()
    {
        /** @var  SignatureConnecteur $connecteur */
        $connecteur = $this->getMyConnecteur();
        return $connecteur->getSousType();
    }

    public function getElementId(): string
    {
        return 'subtype_id';
    }

    public function getElementName(): string
    {
        return 'subtype_name';
    }

    public function getTitle(): string
    {
        return 'Sélectionner un circuit';
    }
}
