<?php

class DocumentTypeControllerTest extends PastellTestCase {

	public function testListAction(){
		/** @var DocumentTypeController $documentTypeController */
		$documentTypeController = $this->getObjectInstancier()->getInstance('DocumentTypeController');
		$documentTypeController->setUtilisateurId(1);
		$list = $documentTypeController->listAction();
		$this->assertEquals('Mail sécurisé',$list['mailsec']['nom']);
	}

}