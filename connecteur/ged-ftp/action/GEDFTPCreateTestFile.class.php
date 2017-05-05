<?php

class GEDFTPCreateTestFile extends ActionExecutor {
	
	public function go(){
		$ged = $this->getMyConnecteur();
		if ( ! $ged){
			$this->setLastMessage("Impossible de se connecter au serveur FTP");
			return false;
		}
		
		$i = mt_rand(0, mt_getrandmax());
		$document_filename = "ged_ftp_document_test_$i.txt"; 
		$ged->addDocument($document_filename,false,false,"test",$ged->getRootFolder());
		
		$this->setLastMessage("Document $document_filename envoyé sur le serveur FTP");
		return true;
	}
	
}