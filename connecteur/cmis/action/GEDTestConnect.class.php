<?php

class GEDTestConnect extends ActionExecutor {
	
	public function go(){
		
		$cmis = $this->getMyConnecteur();
		
		$info = $cmis->getRepositoryInfo();
		
		if (! $info){
			$this->setLastMessage("La connexion avec la GED a �chou� : " . $cmis->getLastError());
			return false;
		}

		$message ="La connexion est r�ussi - Pastell a r�cup�r� les informations suivantes :<ul>" ;
		
		foreach($cmis->getRepositoryRetrieveInfo() as $repoInfo){
					$message .= "<li> $repoInfo  : ".$info[$repoInfo] ."</li>";
		}
		$message .="</ul>";
		$this->setLastMessage($message);
		return true;
	}
	
}