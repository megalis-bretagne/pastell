<?php

declare(strict_types=1);

class GetTypePastell extends DictionnaryChoice
{
    public function getElementId(): string
    {
        return 'pastell_module_id';
    }

    public function getElementName(): string
    {
        return 'pastell_module_name';
    }

    public function getTitle(): string
    {
        return 'Sélectionner un type de dossier d\'importation';
    }

    public function displayAPI()
    {
        /** @var RecupFinParapheur $recupParapheur */
        $recupParapheur = $this->getMyConnecteur();
        $available_types[] = 'ls-recup-parapheur';
        $all_types = array_keys($recupParapheur->getAllFluxRecup());
        $basic_types = array_keys($this->apiGet('/flux', []));
        return array_merge($available_types, array_diff($all_types, $basic_types));
    }
}
