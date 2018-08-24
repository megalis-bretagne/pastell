<?php

class RecupReponsePrefecture extends ActionExecutor {
	
	public function go(){
		/** @var S2low $s2low */
		$s2low = $this->getMyConnecteur();
		$s2low->getListDocumentPrefecture();
		$this->setLastMessage("Les réponses de la préfecture ont été récupérées");
		return true;
	}
	
}