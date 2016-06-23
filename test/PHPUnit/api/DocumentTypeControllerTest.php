<?php

class DocumentTypeControllerTest extends PastellTestCase {

	public function testListAction(){

		/** @var BaseAPIControllerFactory $baseAPIControllerFactory */
		$baseAPIControllerFactory = $this->getObjectInstancier()->getInstance('BaseAPIControllerFactory');
		/** @var DocumentTypeController $documentTypeController */
		$documentTypeController = $baseAPIControllerFactory->getInstance('DocumentType',1);

		$list = $documentTypeController->listAction();
		$this->assertEquals('Mail sécurisé',$list['mailsec']['nom']);
	}

}