<?php

require_once __DIR__ . '/../../../connecteur-type/utilities/DictionnaryChoice.class.php';

class SousTypeNameAction extends DictionnaryChoice
{
    public function getElementId(): string
    {
        return 'sous_type_id';
    }

    public function getElementName(): string
    {
        return 'sous_type_name';
    }

    public function getTitle(): string
    {
        return 'Sélectionner un sous-type';
    }

    public function displayAPI()
    {
        return ['aaa' => 'Arrêtés', 'bbb' => 'Délibérations', 'ccc' => 'Autres'];
    }
}
