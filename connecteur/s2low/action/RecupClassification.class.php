<?php

class RecupClassification extends ActionExecutor {
	
	public function go(){
		$s2low = $this->getMyConnecteur();
		$classification = $s2low->getClassification();
		$this->getConnecteurProperties()->addFileFromData("classification_file","classification.xml",$classification);
		$this->setLastMessage("La classification a �t� mise � jour");
		return true;
	}
	
}