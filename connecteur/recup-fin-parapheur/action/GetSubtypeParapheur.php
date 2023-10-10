<?php

declare(strict_types=1);

class GetSubtypeParapheur extends DictionnaryChoice
{
    public function getElementId(): string
    {
        return 'iparapheur_subtype_id';
    }

    public function getElementName(): string
    {
        return 'iparapheur_subtype_name';
    }

    public function getTitle(): string
    {
        return 'Sélectionner un sous-type de dossier';
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

        if ($recupParapheur->getConnecteurConfig()->get('pastell_module_id') === '') {
            throw new Exception('Veuillez définir un type de dossier');
        }

        $subtype_list = $recupParapheur->getTenantList(); //!!!! placeholder !!!! à remplacer par la liste de sous types
        if (count($subtype_list) === 0) {
            throw new Exception('Aucun sous-type n\'est associé à ce type de dossier');
        }
        return $subtype_list;
    }
}
