<?php

class TypeDossierData {

	public $nom = '';
	public $type = '';
	public $description = '';
	public $nom_onglet = '';

	/** @var TypeDossierFormulaireElement[] */
	public $formulaireElement = [];

	/** @var TypeDossierEtape[] */
	public $etape = [];

}