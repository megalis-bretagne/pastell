<?php

require_once( __DIR__ ."/../lib/ClassificationActes.class.php");


class Classification extends ChoiceActionExecutor {
	
	public function go(){
		$recuperateur = new Recuperateur($_GET);
		$classif = $recuperateur->get('classif');
		$this->getDonneesFormulaire()->setData('classification',$classif);
	}
	
	public function displayAPI(){
		$classificationActes = $this->getClassificationActes();
		return $classificationActes->getAll();
	}
	
	public function display() {
		$this->{'classificationActes'} = $this->getClassificationActes();
		$this->renderPage("Choix de la classification en matière et sous matière",__DIR__."/../template/ChoixClassification.php");
        return true;
	}
	
	private function getClassificationActes(){
		$donneesFormulaire = $this->getConnecteurFactory()->getConnecteurConfigByType($this->id_e,$this->type,'TdT');
		if (! $donneesFormulaire){
			throw new Exception("La classification en matière et sous-matière n'est pas disponible");
		}
		$file = $donneesFormulaire->getFilePath('classification_file');
		if (! file_exists($file)){
			throw new Exception("La classification en matière et sous-matière n'est pas disponible ($file)");
		}
		return new ClassificationActes($donneesFormulaire->getFilePath('classification_file'));
		
	}
	
	
}
