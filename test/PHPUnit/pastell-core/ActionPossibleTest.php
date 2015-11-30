<?php

class ActionPossibleTest extends PastellTestCase {

	public function reinitDatabaseOnSetup(){
		return true;
	}

	public function reinitFileSystemOnSetup(){
		return true;
	}
	
	private function getActionPossible(){
		return new ActionPossible($this->getObjectInstancier());
	}
	
	public function testDroitComplementaireNotPossible(){
		$this->getObjectInstancier()->RoleSQL->addDroit('admin','test:edition');
		
		$apiAction = new APIAction($this->getObjectInstancier(), PastellTestCase::ID_U_ADMIN);
		$info = $apiAction->createDocument(PastellTestCase::ID_E_COL, 'test');
		$this->assertNotEmpty($info['id_d']);
		
		$this->assertFalse($this->getActionPossible()->isActionPossible(PastellTestCase::ID_E_COL, PastellTestCase::ID_U_ADMIN, $info['id_d'], 'teletransmission'));		
	}

	public function testDroitComplementaire(){
		$this->getObjectInstancier()->RoleSQL->addDroit('admin','test:edition');
		$this->getObjectInstancier()->RoleSQL->addDroit('admin','test:teletransmettre');
	
		$apiAction = new APIAction($this->getObjectInstancier(), PastellTestCase::ID_U_ADMIN);
		$info = $apiAction->createDocument(PastellTestCase::ID_E_COL, 'test');
		$this->assertNotEmpty($info['id_d']);
	
		$this->assertTrue($this->getActionPossible()->isActionPossible(PastellTestCase::ID_E_COL, PastellTestCase::ID_U_ADMIN, $info['id_d'], 'teletransmission'));
	}
	
	
}
