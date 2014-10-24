<?php

class OpenIDTestLogin extends ActionExecutor {

	public function go(){
		$openID = $this->getMyConnecteur();
		$login = $openID->authenticate();
		if (!$login){
			$this->setLastMessage("Aucune session en cours");
			return false;
		}
		$this->setLastMessage("Authentifi� avec le login : $login");
		return true;
	}

}