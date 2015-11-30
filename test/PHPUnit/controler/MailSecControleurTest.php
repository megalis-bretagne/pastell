<?php

class MailSecControlerTest extends PastellTestCase {
	
	public function reinitDatabaseOnSetup(){
		return true;
	}
	
	public function reinitFileSystemOnSetup(){
		return true;
	}
	
	public function testAnnuaire(){
		$this->getObjectInstancier()->Authentification->Connexion('admin',1);
		$this->expectOutputRegex("##");
		$this->getObjectInstancier()->MailSecControler->setDontRedirect(true);
		$this->getObjectInstancier()->MailSecControler->annuaireAction();
	}
	
	
}