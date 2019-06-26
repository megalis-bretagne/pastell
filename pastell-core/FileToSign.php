<?php

class FileToSign
{
    /** @var Fichier $document */
    public $document;

    /** @var Fichier[] $annexes */
    public $annexes = [];

    public $type;

    public $sousType;

    public $circuit;

    public $dossierId;

    /** @var Fichier $visualPdf */
    public $visualPdf;

    public $date_limite = false;

    public $xPathPourSignatureXML = false;
    public $annotationPublic = "";
    public $annotationPrivee = "";
    public $emailEmetteur = "";
    public $metadata = [];
    public $dossierTitre = "";
    public $signature_content = "";
    public $signature_type = "";
}
