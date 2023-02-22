<?php

class TdtActes
{
    public $acte_nature;
    public $numero_de_lacte;
    public $objet;
    public $date_de_lacte;
    public $document_papier;
    public $type_acte;
    public $type_pj;
    public $classification;

    /** @var Fichier */
    public $arrete;

    /** @var Fichier[] */
    public $autre_document_attache = [];


    public static function getStringAttributesList()
    {
        return ['acte_nature','numero_de_lacte','objet','date_de_lacte','document_papier','type_acte','type_pj','classification'];
    }
}
