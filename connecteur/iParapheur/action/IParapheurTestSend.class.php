<?php

class IParapheurTestSend extends ActionExecutor {
	
	public function go(){
			
		$iParapheur = $this->getMyConnecteur();	
		$sous_type = $iParapheur->getSousType();
		
		$result = $iParapheur->sendDocumentTest();		
		
		if (! $result){
			$errorActe = $iParapheur->getLastError();
			$result = $iParapheur->sendDocumentTestHelios();
			if (! $result){
				$errorHelios = $iParapheur->getLastError();
				$this->setLastMessage("La connexion avec le iParapheur a échoué (sous_type ".$sous_type[0]."): \n"."TEST_1: ". $errorActe."\n"."TEST_2: ". $errorHelios);
				return false;
			}
		}		
		$this->setLastMessage("Document sous_type ".$sous_type[0].": ".$result);
		return true;
	}
	
}