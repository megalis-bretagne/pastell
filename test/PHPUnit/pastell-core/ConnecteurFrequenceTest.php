<?php

class ConnecteurFrequenceTest extends PHPUnit_Framework_TestCase {

	public function testConstruct(){
		$connecteurFrequence = new ConnecteurFrequence(array('type_connecteur'=>'toto','id_cf'=>12));
		$this->assertEquals('toto',$connecteurFrequence->type_connecteur);
	}

	public function testGetArray(){
		$connecteurFrequence = new ConnecteurFrequence(array('type_connecteur'=>'toto','id_cf'=>12));
		$this->assertEquals('toto',$connecteurFrequence->getArray()['type_connecteur']);
	}

	public function testGetConnecteurSelectorAll(){
		$connecteurFrequence = new ConnecteurFrequence();
		$this->assertEquals("Tous les connecteurs",$connecteurFrequence->getConnecteurSelector());
	}

	public function testGetConnecteurSelectorGlobal(){
		$connecteurFrequence = new ConnecteurFrequence();
		$connecteurFrequence->type_connecteur = ConnecteurFrequence::TYPE_GLOBAL;
		$this->assertEquals("(Global) Tous les connecteurs",$connecteurFrequence->getConnecteurSelector());
	}

	public function testGetConnecteurSelectorEntite(){
		$connecteurFrequence = new ConnecteurFrequence();
		$connecteurFrequence->type_connecteur = ConnecteurFrequence::TYPE_ENTITE;
		$this->assertEquals("(Entité) Tous les connecteurs",$connecteurFrequence->getConnecteurSelector());
	}

	public function testGetConnecteurSelectorFamille(){
		$connecteurFrequence = new ConnecteurFrequence();
		$connecteurFrequence->id_ce = 1;
		$connecteurFrequence->type_connecteur = ConnecteurFrequence::TYPE_ENTITE;
		$connecteurFrequence->famille_connecteur = "signature";
		$this->assertEquals("(Entité) signature",$connecteurFrequence->getConnecteurSelector());
	}

	public function testGetConnecteurSelectorConnecteur(){
		$connecteurFrequence = new ConnecteurFrequence();
		$connecteurFrequence->id_ce = 1;
		$connecteurFrequence->type_connecteur = ConnecteurFrequence::TYPE_ENTITE;
		$connecteurFrequence->famille_connecteur = "signature";
		$connecteurFrequence->id_connecteur = "i-parapheur";
		$this->assertEquals("(Entité) signature:i-parapheur",$connecteurFrequence->getConnecteurSelector());
	}

	public function testGetActionSelectorAll(){
		$connecteurFrequence = new ConnecteurFrequence();
		$this->assertEquals("Toutes les actions",$connecteurFrequence->getActionSelector());
	}

	public function testGetActionSelectorType(){
		$connecteurFrequence = new ConnecteurFrequence();
		$connecteurFrequence->action_type = ConnecteurFrequence::TYPE_ACTION_CONNECTEUR;
		$this->assertEquals("(Connecteur) toutes les actions",$connecteurFrequence->getActionSelector());
	}

	public function testGetActionSelectorAction(){
		$connecteurFrequence = new ConnecteurFrequence();
		$connecteurFrequence->action_type = ConnecteurFrequence::TYPE_ACTION_CONNECTEUR;
		$connecteurFrequence->action = 'recup-type';
		$this->assertEquals("(Connecteur) recup-type",$connecteurFrequence->getActionSelector());
	}

	public function testGetActionSelectorDocumentAll(){
		$connecteurFrequence = new ConnecteurFrequence();
		$connecteurFrequence->action_type = ConnecteurFrequence::TYPE_ACTION_DOCUMENT;
		$this->assertEquals("(Document) Tous les types de documents",$connecteurFrequence->getActionSelector());
	}

	public function testGetActionSelectorDocument(){
		$connecteurFrequence = new ConnecteurFrequence();
		$connecteurFrequence->action_type = ConnecteurFrequence::TYPE_ACTION_DOCUMENT;
		$connecteurFrequence->type_document = 'actes-generique';
		$this->assertEquals("(Document) actes-generique: toutes les actions",$connecteurFrequence->getActionSelector());
	}

	public function testGetActionSelectorDocumentAction(){
		$connecteurFrequence = new ConnecteurFrequence();
		$connecteurFrequence->action_type = ConnecteurFrequence::TYPE_ACTION_DOCUMENT;
		$connecteurFrequence->type_document = 'actes-generique';
		$connecteurFrequence->action = 'verif-signature';
		$this->assertEquals("(Document) actes-generique: verif-signature",$connecteurFrequence->getActionSelector());
	}



}