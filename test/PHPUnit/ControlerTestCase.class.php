<?php

class ControlerTestCase extends PastellTestCase {

	/**
	 * @var PastellControler
	 */
	private $controler;

	protected function setGetInfo(array $info){
		$this->controler->setGetInfo(new Recuperateur($info));
	}

	public function getControlerInstance($class_name){
		$this->getObjectInstancier()->Authentification->Connexion('admin',1);
		$this->controler = $this->getObjectInstancier()->getInstance($class_name);
		$this->controler->setDontRedirect(true);
		return $this->controler;
	}


}