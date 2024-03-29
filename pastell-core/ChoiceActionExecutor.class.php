<?php
abstract class ChoiceActionExecutor extends ActionExecutor {

	private $viewParameter;
	protected $field;

	private $recuperateur;


	public function __construct(ObjectInstancier $objectInstancier){
		parent::__construct($objectInstancier);
		$this->viewParameter = array();
		$this->setRecuperateur(new Recuperateur($_POST));
	}

	public function setRecuperateur(Recuperateur $recuperateur){
	    $this->recuperateur = $recuperateur;
    }

	public function getRecuperateur() : Recuperateur {
        return $this->recuperateur;
    }

	public function setField($field){
		$this->field = $field;
	}
	
	public function __set($key,$value){
		$this->viewParameter[$key] = $value;
		$this->$key  = $value;
	}
	
	public function getViewParameter(){
		$this->viewParameter['id_d'] = $this->id_d;
		$this->viewParameter['id_e'] = $this->id_e;
		$this->viewParameter['id_ce'] = $this->id_ce;	
		$this->viewParameter['action'] = $this->action;
		$this->viewParameter['field'] = $this->field;	
		return $this->viewParameter;
	}
	
	public function renderPage($page_title,$template){
	    $this->displayMenuGauche();
		$this->page_title = $page_title;
		$this->template_milieu = $template;
		$this->objectInstancier->PastellControler->setAllViewParameter($this->getViewParameter());
		$this->objectInstancier->PastellControler->setNavigationInfo($this->id_e,"/Entite/connecteur");
		$this->objectInstancier->PastellControler->renderDefault();
	}
	
	public function redirectToFormulaire(){
		header("Location: edition?id_d={$this->id_d}&id_e={$this->id_e}&page={$this->page}");
	}
	
	public function redirectToConnecteurFormulaire(){
		header("Location: editionModif?id_ce={$this->id_ce}");
	}


    public function displayMenuGauche() {
        if (! $this->id_ce) {
            return;
        }
        $this->{'id_e_menu'} = $this->id_e;
        $this->{'type_e_menu'} = "";
        $this->{'menu_gauche_template'} = "EntiteMenuGauche";
        $this->{'menu_gauche_select'} = "Entite/connecteur";
    }


	public function isEnabled(){
		return true;
	}
	
	abstract public function display() ;
	
	abstract public function displayAPI();
	
}