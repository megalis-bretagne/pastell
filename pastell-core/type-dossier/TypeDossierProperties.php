<?php

class TypeDossierProperties
{
    public $id_type_dossier = '';
    public $nom = '';
    public $type = '';
    public $description = '';
    public $nom_onglet = '';
    public bool $affiche_one = false;
    public $restriction_pack = '';

    /** @var TypeDossierFormulaireElementProperties[] */
    public $formulaireElement = [];

    /** @var TypeDossierEtapeProperties[] */
    public $etape = [];
}
