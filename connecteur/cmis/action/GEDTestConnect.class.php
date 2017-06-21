<?php

class GEDTestConnect extends ActionExecutor {
	
	public function go(){

		/** @var CMIS $cmis */
		$cmis = $this->getMyConnecteur();
		
		$info = $cmis->getRepositoryInfo();
		
		if (! $info){
			$this->setLastMessage("La connexion avec la GED a échoué : " . $cmis->getLastError());
			return false;
		}

		$message ="La connexion est réussie - Pastell a récupéré les informations suivantes :<ul>" ;

		foreach($info as $repoInfo => $data){

			if (is_array($data)){
				continue;
			}
			$message .= "<li> $repoInfo  : $data </li>";
		}
		$message .="</ul>";
		$this->setLastMessage($message);
		return true;
	}
	
}