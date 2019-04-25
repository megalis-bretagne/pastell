<?php 
class IparapheurSousType extends ChoiceActionExecutor {
	
	public function go(){

		$recuperateur = $this->getRecuperateur();

		$stringMapping = $this->getIdMapping();

		$iparapheur_sous_type_element = $stringMapping->get('iparapheur_sous_type');
		$iparapheur_type_element = $stringMapping->get('iparapheur_type');

		$sous_type_iparapheur = $recuperateur->get('iparapheur_sous_type');
		$signature_config = $this->getConnecteurConfigByType('signature');
		$type_iparapheur = $signature_config->get('iparapheur_type');

		$donneesFormulaire = $this->getDonneesFormulaire();
		$donneesFormulaire->setData($iparapheur_type_element,$type_iparapheur);
		$donneesFormulaire->setData($iparapheur_sous_type_element,$sous_type_iparapheur);
	}
	
	public function displayAPI(){
		return $this->getSousType();
	}
	
	public function display(){
		$this->{'sous_type'} = $this->getSousType();
		$this->renderPage(
			"Choix d'un type de document",
			__DIR__."/../../../connecteur/iParapheur/template/IparapheurSousType.php"
		);
		return true;
	}
	
	private function getSousType(){
		/** @var SignatureConnecteur $signature */
		$signature = $this->getConnecteur('signature');
		return $signature->getSousType();
	}

	public function displayChoiceForSearch(){
		$result = array();
		$config= $this->getConnecteurConfigByType('signature');
		$data = explode("\n",$config->getFileContent('iparapheur_sous_type'));
		foreach($data as $key => $name){
			if ($name) {
				$result[$name] = $name;
			}
		}
		return $result;
	}
}