<?php
class FakeNomemclatureListe extends ChoiceActionExecutor {
	
	public function go(){
		$recuperateur = new Recuperateur($_GET);
		$fieldValue = $recuperateur->get($this->field);
		
		$donneesFormulaire = $this->objectInstancier->ConnecteurFactory->getConnecteurConfig($this->id_ce);
		$donneesFormulaire->setData($this->field,$fieldValue);
	}
	
	public function displayAPI(){}
	
	public function display(){
		$id_e_cdg = $this->objectInstancier->EntiteSQL->getCDG($this->id_e);
		
		$donneesFormulaire = $this->objectInstancier->ConnecteurFactory->getConnecteurConfigByType($id_e_cdg,'actes-cdg','classification-cdg');
		
		if (! $donneesFormulaire){
			throw new Exception("Aucun connecteur classification-cdg (flux actes-cdg) trouvé pour le centre de gestion de cette entité");
		}
		
		$this->classifCDG = $donneesFormulaire->get("classification_cdg");
		
		$this->renderPage("Fichier de nomemclauture", __DIR__."/../../s2low/template/NomemclatureListeSelect.php");
		return true;
	}
	
	
}