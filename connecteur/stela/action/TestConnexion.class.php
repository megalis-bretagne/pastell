<?php


class TestConnexion extends ActionExecutor {
	
	public function go(){
		$stela = $this->getMyConnecteur();
		$result = $stela->testConnexion();
		$this->setLastMessage("La connexion est r�ussie : " . $result);
		return true;
	}
	
}