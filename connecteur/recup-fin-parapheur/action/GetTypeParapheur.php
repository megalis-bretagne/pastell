<?php

declare(strict_types=1);

class GetTypeParapheur extends DictionnaryChoice
{
    public function getElementId(): string
    {
        return 'iparapheur_type_id';
    }

    public function getElementName(): string
    {
        return 'iparapheur_type_name';
    }

    public function getTitle(): string
    {
        return 'Sélectionner un type de dossier';
    }

    /**
     * @throws Exception
     */
    public function displayAPI(): array
    {
        /** @var RecupFinParapheur $recupParapheur */
        $recupParapheur = $this->getMyConnecteur();

        if ($recupParapheur->getConnecteurConfig()->get('tenant_id') === '') {
            throw new Exception('Veuillez définir une entité');
        }

        $subtype_list = $recupParapheur->getTenantList(); //!!!! placeholder !!!! à remplacer par la liste de types
        if (count($subtype_list) === 0) {
            throw new Exception('Aucun type de dossier n\'est associé à cette entité');
        }
        return $subtype_list;
    }
}
