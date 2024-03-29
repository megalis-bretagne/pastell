<?php

class ActionExecutorTest extends PastellTestCase {

	/**
	 * @return ActionExecutor
	 */
	private function getActionExecutor(){
		$concreteActionExecutor = $this->getMockForAbstractClass('ActionExecutor',array($this->getObjectInstancier()));
		return $concreteActionExecutor;
	}
	
	/**
	 * @expectedException Exception
	 * @expectedExceptionMessage Aucun connecteur de type blutrepoi n'est associé au flux actes-generique
	 */
	public function testNoConnecteur(){
		$concreteActionExecutor = $this->getActionExecutor();
		$concreteActionExecutor->setEntiteId(1);
		$concreteActionExecutor->setDocumentId('actes-generique', 42);
		$connecteur = $concreteActionExecutor->getConnecteur('blutrepoi');
	}
	
	private function getActionExecutorMailSec(){
		$concreteActionExecutor = $this->getActionExecutor();
		$concreteActionExecutor->setEntiteId(1);
		$concreteActionExecutor->setDocumentId('mailsec', 42);
		return $concreteActionExecutor;
	}
	
	public function testGetConnecteur(){
		$this->getObjectInstancier()->Document->save(42,'mailsec');
		$concreteActionExecutor = $this->getActionExecutorMailSec();
		$connecteur = $concreteActionExecutor->getConnecteur('mailsec');
		$this->assertInstanceOf('MailSec', $connecteur);
	}
	
	public function testGetConnecteurConfigByType(){
		$this->getObjectInstancier()->Document->save(42,'mailsec');
		$concreteActionExecutor = $this->getActionExecutorMailSec();
		$connecteur_config = $concreteActionExecutor->getConnecteurConfigByType('mailsec');
		$this->assertEquals('pastell@sigmalis.com', $connecteur_config->getWithDefault('mailsec_from'));
	}
	
	public function testGetConnecteurConfig(){
		$concreteActionExecutor = $this->getActionExecutorMailSec();
		$connecteur_config = $concreteActionExecutor->getConnecteurConfig(11);
		$this->assertEquals('pastell@sigmalis.com', $connecteur_config->getWithDefault('mailsec_from'));
	}


	
}