<?php

require_once __DIR__ . '/../../../connecteur-type/utilities/DictionnaryChoice.class.php';

class BureauNameAction extends DictionnaryChoice
{
    public function getElementId(): string
    {
        return 'bureau_id';
    }

    public function getElementName(): string
    {
        return 'bureau_name';
    }

    public function getTitle(): string
    {
        return 'Sélectionner un bureau';
    }

    public function displayAPI()
    {
        return ['1233' => 'Bureau du maire', 'çàçà)çà' => 'Bureau du DGS', 'xxxx' => 'Bureau autre'];
    }
}
