<?php

require_once __DIR__ . '/../../../connecteur-type/utilities/DictionnaryChoice.class.php';

class TypeNameAction extends DictionnaryChoice
{
    public function getElementId(): string
    {
        return 'type_id';
    }

    public function getElementName(): string
    {
        return 'type_name';
    }

    public function getTitle(): string
    {
        return 'Sélectionner un type';
    }

    public function displayAPI()
    {
        return ['AAAAA' => 'Actes', 'BBBB' => 'PES', 'CCC' => 'Courrier'];
    }
}
