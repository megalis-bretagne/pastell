<?php

class ConnecteurAPIControllerTest extends PastellTestCase {

	public function testListAction(){
		$list = $this->getInternalAPI()->get("/entite/0/connecteur");
		$this->assertEquals('horodateur-interne',$list[0]['id_connecteur']);
	}

	public function testGetBadEntiteConnecteur(){
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Le connecteur 12 n'appartient pas à l'entité 2");
		$this->getInternalAPI()->get("/entite/2/connecteur/12");
	}

	public function testGetBadEntite(){
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage("L'entité 42 n'existe pas");
		$this->getInternalAPI()->get("/entite/42/connecteur");
	}

	public function testCreate(){
		$info = $this->getInternalAPI()->post("/entite/1/connecteur", array('libelle'=>'Connecteur de test','id_connecteur'=>'test'));
		$this->assertEquals('Connecteur de test',$info['libelle']);
	}

	public function testCreateWithoutLibelle(){
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Le libellé est obligatoire.");
		$this->getInternalAPI()->post("/entite/1/connecteur", array('libelle'=>'','id_connecteur'=>'test'));
	}

	public function testCreateGlobale(){
		$info = $this->getInternalAPI()->post("/entite/0/connecteur", array('libelle'=>'Test','id_connecteur'=>'test'));
		$this->assertEquals(0,$info['id_e']);
	}

	public function testCreateNotExist(){
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Aucun connecteur de ce type.");
		$this->getInternalAPI()->post("/entite/1/connecteur", array('libelle'=>'Connecteur de test','id_connecteur'=>'foo'));
	}

	public function testDelete(){
		$info = $this->getInternalAPI()->delete("/entite/1/connecteur/12");
		$this->assertEquals("ok",$info['result']);
	}

	public function testDeleteNotExist(){
		$this->setExpectedException("Exception","Ce connecteur n'existe pas.");
		$this->getInternalAPI()->delete("/entite/1/connecteur/42");
	}

	public function testDeleteUsed(){
		$this->setExpectedException("Exception","Ce connecteur est utilisé par des flux :  actes-generique");
		$this->getInternalAPI()->delete("/entite/1/connecteur/1");
	}

	public function testEdit(){
		$info = $this->getInternalAPI()->patch("/entite/1/connecteur/12",array('libelle'=>'bar'));
		$this->assertEquals('bar',$info['libelle']);
	}

	public function testEditNotExist(){
		$this->setExpectedException("Exception","Ce connecteur n'existe pas.");
		$this->getInternalAPI()->patch("/entite/1/connecteur/42",array('libelle'=>'bar'));
	}

	public function testEditNotLibelle(){
		$this->setExpectedException("Exception","Le libellé est obligatoire.");
		$this->getInternalAPI()->patch("/entite/1/connecteur/12",array('libelle'=>''));
	}

	public function testEditContentAction(){
		$info = $this->getInternalAPI()->patch("/entite/1/connecteur/12/content",array('champs1'=>'foo'));
		$this->assertEquals('foo',$info['data']['champs1']);
	}

	public function testEditContentOnChangeAction(){
		$info = $this->getInternalAPI()->patch("/entite/1/connecteur/12/content",array('champs3'=>'foo'));
		$this->assertEquals('foo',$info['data']['champs4']);
	}

	public function testPostFile(){
	    $result = $this->getInternalAPI()->post("/entite/1/connecteur/12/file/champs5",
            array(
                'file_name'=>'test.txt',
                'file_content'=>'test...'
            )
        );
        $this->assertEquals("test.txt",$result['data']['champs5'][0]);
        $this->expectOutputRegex("#test...#");
        $this->setExpectedException("Exception","Exit called with code 0");
        $this->getInternalAPI()->get("/entite/1/connecteur/12/file/champs5");
    }

}