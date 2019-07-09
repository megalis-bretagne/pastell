<?php

class ControlerTestCase extends PastellTestCase {

	/**
	 * @var PastellControler
	 */
	private $controler = null;

	private $get_info = [];
	private $post_info = [];

	protected function setGetInfo(array $info){
		if($this->controler) {
			$this->controler->setGetInfo(new Recuperateur($info));
		}
		$this->get_info = $info;
	}

	protected function setPostInfo(array $info)
	{
		if ($this->controler) {
			$this->controler->setPostInfo(new Recuperateur($info));
		}
		$this->post_info = $info;

	}

	public function getControlerInstance($class_name){
		$this->getObjectInstancier()->Authentification->Connexion('admin',1);
		$this->controler = $this->getObjectInstancier()->getInstance($class_name);
		$this->controler->setDontRedirect(true);
		$this->controler->setGetInfo(new Recuperateur($this->get_info));
		$this->controler->setPostInfo(new Recuperateur($this->post_info));
		return $this->controler;
	}

}