<?php

require_once( PASTELL_PATH . "/lib/system/CMIS.class.php");

class GEDTestFolder extends ActionExecutor {
	
	public function go(){
				
		$donneesFormulaire = $this->getDonneesFormulaire();
		
		$activate = $donneesFormulaire->get('ged_activate');
		
		if (! $activate){
			$this->setLastMessage("La connexion avec la GED a �chou� : le module n'est pas activ�");
			return false;
		}
		
		$url = $donneesFormulaire->get('ged_url');
		$login = $donneesFormulaire->get('ged_user_login');
		$password = $donneesFormulaire->get('ged_user_password');
		$folder = $donneesFormulaire->get('ged_folder');
		
		$cmis = new CMIS($url,$login,$password);
		$info = $cmis->getObjectByPath($folder);
		
		if (! $info){
			$this->setLastMessage("La connexion avec la GED a �chou� : " . $cmis->getLastError());
			return false;
		}

		$message ="La connexion est r�ussi - Pastell a r�cup�r� les informations suivantes :<ul>" ;
		
		foreach($cmis->getFolderRetrieveInfo() as $repoInfo){
					$message .= "<li> $repoInfo  : ".$info[$repoInfo] ."</li>";
		}
		$message .="</ul>";
		$this->setLastMessage($message);
		return true;
	}
	
}