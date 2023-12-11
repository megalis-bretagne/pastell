<?php

declare(strict_types=1);

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
        return 'Sélectionner une entité';
    }

    /**
     * @throws Exception
     */
    public function displayAPI(): array
    {
        /** @var RecupParapheurCorbeille $recupParapheur */
        $recupParapheur = $this->getMyConnecteur();
        return $recupParapheur->getTenantList();
    }
}
