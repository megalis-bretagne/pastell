<?php 


class CASTestLogout extends ActionExecutor {
	
	public function go(){
		$cas = $this->getMyConnecteur();
		$login = $cas->logout(SITE_BASE);
		$this->setLastMessage("D�connect� avec succ�s");
		return true;
	}
	
}