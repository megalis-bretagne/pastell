<?php

class TypeDossierProperties
{
    public $id_type_dossier = '';
    public $nom = '';
    public $type = '';
    public $description = '';
    public $nom_onglet = '';
    public $restriction_pack = '';

    /** @var TypeDossierFormulaireElementProperties[] */
    public $formulaireElement = [];

    /** @var TypeDossierEtapeProperties[] */
    public $etape = [];
}
