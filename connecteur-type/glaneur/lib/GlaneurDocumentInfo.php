<?php

class GlaneurDocumentInfo
{
    public function __construct(int $id_e)
    {
        $this->id_e = $id_e;
    }

    public $id_e;

    public $nom_flux;
    public $element_files_association = [];
    public $metadata = [];
    public $force_action_ok;
    public $action_ok;
    public $action_ko;
}
