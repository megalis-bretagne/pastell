<?php

class OpenIDTestLogout extends ActionExecutor {

	public function go(){
		$openID = $this->getMyConnecteur();
		$openID->logout();
		$this->setLastMessage("Vous avez �t� d�connect�");
		return true;
	}

}