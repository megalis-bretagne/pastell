<?php

class TypeDossierData {

	public $nom;
	public $type;
	public $description;

	/** @var TypeDossierFormulaireElement[] */
	public $formulaireElement;

	/** @var TypeDossierCheminementElement[] */
	public $cheminementElement;

}