<?php

require_once __DIR__ . '/../../../connecteur-type/utilities/DictionnaryChoice.class.php';

class TenantNameAction extends DictionnaryChoice
{
    public function getElementId(): string
    {
        return 'tenant_id';
    }

    public function getElementName(): string
    {
        return 'tenant_name';
    }

    public function getTitle(): string
    {
        return 'Sélectionner un tenant';
    }

    public function displayAPI()
    {
        /** @var RecupParapheur $recupParapheur */
        $recupParapheur = $this->getMyConnecteur();
        return $recupParapheur->getTenantList();
    }
}
