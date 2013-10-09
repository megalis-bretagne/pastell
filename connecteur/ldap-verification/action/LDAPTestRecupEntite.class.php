<?php 

class LDAPTestRecupEntite extends ActionExecutor {

	public function go(){
		$ldap = $this->getMyConnecteur();
		$login = $this->objectInstancier->Authentification->getLogin();
		$entry = $ldap->getEntite($login);
		if (!$entry){
			throw new Exception("Aucune entit� trouv� pour $login");
		}
		$this->setLastMessage("Entit� trouv� : " . $entry);
		return true;
	}

}