<?php

class SignatureGetSousType extends DictionnaryChoice
{

    /**
     * @throws Exception
     */
    public function displayAPI()
    {
        dump($this->getMyConnecteur()->getSousType());
        die();
        return $this->getMyConnecteur()->getSousType();
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