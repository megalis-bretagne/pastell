<?php
/**
 * Gère le contenu d'un fichier definition.yml d'un flux
 */
class DocumentType {
	
	const NOM = 'nom';
	const TYPE_FLUX = 'type';
	const DESCRIPTION = 'description';

	const FORMULAIRE = 'formulaire';
	const ACTION = 'action';
	const PAGE_CONDITION = 'page-condition';
	const AFFICHE_ONE = 'affiche_one';
	const CONNECTEUR = 'connecteur';
	const CHAMPS_AFFICHE = 'champs-affiches';
	const CHAMPS_RECHERCHE_AFFICHE = 'champs-recherche-avancee';
	
	const TYPE_FLUX_DEFAULT = 'Flux Généraux';
	
	private $module_id;
	private $module_definition;
	
	private $formulaire;
	
	public function __construct($module_id,array $module_definition){
		$this->module_id = $module_id; 
		$this->module_definition = $module_definition;
	}

	public function exists(){
		return  !! $this->module_definition; 
	}

	public function getModuleId(){
	    return $this->module_id;
    }

	public function getName(){
		if (empty($this->module_definition[self::NOM])){
			return $this->module_id;
		}
		return $this->module_definition[self::NOM];
	}
	
	public function getDescription(){
		if (empty($this->module_definition[self::DESCRIPTION])){
			return false;
		}
		return $this->module_definition[self::DESCRIPTION];
	}
	
	public function getType(){
		if (empty($this->module_definition[self::TYPE_FLUX])){
			return self::TYPE_FLUX_DEFAULT;
		}
		return $this->module_definition[self::TYPE_FLUX];
	}
	
	public function getConnecteur(){
		if (isset($this->module_definition[self::CONNECTEUR])){
			return $this->module_definition[self::CONNECTEUR];
		}
		return array();
	}
	
	/**
	 * Crée un objet de type Formulaire
	 * @return Formulaire
	 */
	public function getFormulaire(){
		if (!$this->formulaire){
			$this->formulaire = new Formulaire($this->getFormulaireArray());
		} 
		return $this->formulaire;
	}
	
	public function getPageCondition(){
		if (isset($this->module_definition[self::PAGE_CONDITION])) {
			return $this->module_definition[self::PAGE_CONDITION];
		} else {
			return array();
		}
	}	
	
	public function isAfficheOneTab(){
		return (! empty($this->module_definition[self::AFFICHE_ONE]));
	}
	
	private function getFormulaireArray(){
		if (empty($this->module_definition[self::FORMULAIRE])){
			return array();
		}
		return $this->module_definition[self::FORMULAIRE];
	}

	/**
	 * @return Action
	 */
	public function getAction(){
		if (empty($this->module_definition[self::ACTION])){
			return new Action();
		}
		return new Action((array) $this->module_definition[self::ACTION]);
	}
	
	public function getTabAction(){
		if (empty($this->module_definition[self::ACTION])){
			return array();
		}
		return $this->module_definition[self::ACTION];
	}
	
	public function getChampsAffiches(){
		$default_fields = array('titre'=>'Objet','entite'=>'Entité','dernier_etat'=>'Dernier état','date_dernier_etat'=>'Date'); 
		if (empty($this->module_definition[self::CHAMPS_AFFICHE])){
			return $default_fields;
		}
		$result = array();
		foreach( $this->module_definition[self::CHAMPS_AFFICHE] as $champs){
			if (isset($default_fields[$champs])){
				$result[$champs] = $default_fields[$champs];
			} else {
				$field = $this->getFormulaire()->getField($champs);
				if ($field) {
					$result[$champs] = $field->getLibelle();
				} else {
					$result[$champs] = "##ERREUR##";
				}
			}
		}
		return $result;
	}
	
	public function getChampsRechercheAvancee(){
		if (isset($this->module_definition[self::CHAMPS_RECHERCHE_AFFICHE])){
			return $this->module_definition[self::CHAMPS_RECHERCHE_AFFICHE];
		}
		$default_field = array('type','id_e','lastetat','last_state_begin','etatTransit','state_begin','search');
		
		foreach($this->getFormulaire()->getIndexedFields() as $indexField => $indexLibelle){
			$default_field[] = $indexField;
		}
		$default_field[] = 'tri';
		return $default_field;
	}
	
	public function getListDroit(){
		$all_droit = array($this->module_id.":lecture",$this->module_id.":edition");
		$all_droit = array_merge($all_droit,$this->getAction()->getAllDroit());
		$all_droit = array_values(array_unique($all_droit));
		return $all_droit;
	}
	
}