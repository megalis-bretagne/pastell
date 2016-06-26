<?php

class PastellCoreTestExternalData extends ChoiceActionExecutor{

	public function go(){

		$recuperateur = new Recuperateur($_POST);
		$choix = $recuperateur->get('choix');

		$donneesFormulaire = $this->getDonneesFormulaire();
		$donneesFormulaire->setData('test_external_data',$choix);

		return true;
	}

	public function display() {
		$this->renderPage("Choix", __DIR__."/../template/TestChoix.php");
	}

	public function displayAPI() {
		return array('pierre','feuille','ciseaux','lézard','Spock');
	}


}