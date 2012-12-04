<?php


class RecupClassification extends ActionExecutor {
	
	public function go(){
		$stela = $this->getMyConnecteur();
		$classification = $stela->getClassification();
		$this->getConnecteurProperties()->addFileFromData("classification_file","classification.xml",$classification);
		
		$this->setLastMessage("La classification a �t� mise � jour");
		return true;
	}
	
}