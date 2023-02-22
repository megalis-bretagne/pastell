<?php

class TypeDossierEtapeProperties
{
    public $num_etape;
    public $type;
    /** @var string|null $label */
    public $label;
    /** @var bool $defaultChecked */
    public $defaultChecked;
    public $requis;
    public $automatique;

    public $specific_type_info = [];

    public $num_etape_same_type = 0;
    public $etape_with_same_type_exists = false;
}
