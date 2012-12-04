<?php

class IParapheurTest extends ActionExecutor {
	
	public function go(){
			
		$iParapheur = $this->getMyConnecteur();
		
		$result = $iParapheur->testConnexion();
		
		
		if (! $result){
			$this->setLastMessage("La connexion avec le iParapheur a �chou� : " . $iParapheur->getLastError());
			return false;
		}

		$this->setLastMessage("La connexion est r�ussie : ".$result);
		return true;
	}
	
}