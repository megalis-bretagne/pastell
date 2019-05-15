<?php

class FluxControlerTest extends ControlerTestCase {

	/** @var  FluxControler */
	private $fluxControler;

	public function setUp(){
		parent::setUp();
		$this->fluxControler = $this->getControlerInstance("FluxControler");
	}

	/**
	 * @throws NotFoundException
	 */
	public function testIndexActionWithId_e(){
		$this->setGetInfo(['id_e'=>PastellTestCase::ID_E_COL]);
		$this->expectOutputRegex("#Bourg-en-Bresse : Liste des flux - Pastell#");
		$this->fluxControler->indexAction();
	}

	/**
	 * @throws NotFoundException
	 */
	public function testIndexActionFluxWithTwoSameConnecteurType(){
		$this->setGetInfo(['id_e'=>PastellTestCase::ID_E_COL]);
		$this->expectOutputRegex("#/Flux/edition\?id_e=1&flux=test&type=test&num_same_type=1#");
		$this->fluxControler->indexAction();
	}

	/**
	 * @throws NotFoundException
	 */
	public function testIndexActionWithoutId_e(){
		$this->expectOutputRegex("#Entité racine : Liste des flux - Pastell#");
		$this->fluxControler->indexAction();
	}

	/**
	 * @throws NotFoundException
	 */
	public function testEditionActionAucunConnecteur(){
		$this->expectException(LastErrorException::class);
		$this->fluxControler->editionAction();
	}

	/**
	 * @throws NotFoundException
	 */
	public function testEditionAction(){
		$this->getObjectInstancier()->ConnecteurEntiteSQL->addConnecteur(1,'mailsec','mailsec','mailsec-test');
		$this->setGetInfo(array("id_e"=>1,"flux"=>"mailsec","type"=>"mailsec"));
		$this->expectOutputRegex("#mailsec-test#");
		$this->fluxControler->editionAction();
	}

	/**
	 * @throws NotFoundException
	 */
	public function testEditionActionSelection(){
		$id_ce = $this->getObjectInstancier()->ConnecteurEntiteSQL->addConnecteur(1,'mailsec','mailsec','mailsec-test');
		$this->getObjectInstancier()->FluxEntiteSQL->addConnecteur(1,'mailsec','mailsec',$id_ce);
		$this->setGetInfo(array("id_e"=>1,"flux"=>"mailsec","type"=>"mailsec"));
		$this->expectOutputRegex("#checked='checked'#");
		$this->fluxControler->editionAction();
	}

	/**
	 * @throws NotFoundException
	 */
	public function testEditionActionGlobale(){
		$id_ce = $this->getObjectInstancier()->ConnecteurEntiteSQL->addConnecteur(0,'horodateur-interne','horodateur','horodateur-test');
		$this->getObjectInstancier()->FluxEntiteSQL->addConnecteur(0,'global','horodateur',$id_ce);
		$this->setGetInfo(array("id_e"=>0,"flux"=>"","type"=>"horodateur"));
		$this->expectOutputRegex("#checked='checked'#");
		$this->fluxControler->editionAction();
	}

	/**
	 * @throws NotFoundException
	 */
	public function testDoEditionModif(){
		$id_ce = $this->getObjectInstancier()->ConnecteurEntiteSQL->addConnecteur(1,'mailsec','mailsec','mailsec-test');
		$this->setPostInfo(array("id_e"=>1,"flux"=>'mailsec','type'=>'mailsec','id_ce'=>$id_ce));
		$this->expectException(LastMessageException::class);
		$this->fluxControler->doEditionAction();
		$this->assertEquals($id_ce,$this->getObjectInstancier()->FluxEntiteSQL->getConnecteurId(1,'mailsec','mailsec'));
	}
	
	public function testDoEditionModifBadType(){
		$id_ce = $this->getObjectInstancier()->ConnecteurEntiteSQL->addConnecteur(1,'mailsec','mailsec','mailsec-test');
		$this->setPostInfo(array("id_e"=>1,"flux"=>'actes-generique','type'=>'signature','id_ce'=>$id_ce));
		$this->expectException(LastErrorException::class);
		$this->fluxControler->doEditionAction();
		$this->assertNotEquals($id_ce,$this->getObjectInstancier()->FluxEntiteSQL->getConnecteurId(1,'mailsec','mailsec'));
	}
	
	public function testDoEditionDelete(){
		$this->setPostInfo(array("id_e"=>1,"flux"=>'actes-generique','type'=>'signature','id_ce' => 0));
		$this->expectException(LastMessageException::class);
		$this->fluxControler->doEditionAction();
		$this->assertNull($this->getObjectInstancier()->FluxEntiteSQL->getConnecteurId(1,'actes-generique','signature'));
	}
	
	public function testGetListFlux(){
		$result = $this->fluxControler->getListFlux(1);
		$this->assertEquals('test',$result[40]['connecteur_type']);
		$this->assertEquals(1,$result[40][DocumentType::NUM_SAME_TYPE]);
		$this->assertTrue($result[40][DocumentType::CONNECTEUR_WITH_SAME_TYPE]);
		$this->assertNotEmpty($result);
	}
	
	/**
	 * @expectedException Exception
	 * @expectedExceptionMessage Le type de flux « blutrepoi » n'existe pas.
	 */
	public function testEditionModif(){
		$id_ce = $this->getObjectInstancier()->ConnecteurEntiteSQL->addConnecteur(1,'mailsec','mailsec','mailsec-test');
		$this->fluxControler->editionModif(1,'blutrepoi','mailsec', $id_ce);
	}
	
	/**
	 * @expectedException LastMessageException
	 */
	public function testToogle(){
		$this->setPostInfo(array('id_e'=>2,'flux'=>'actes-generique'));
		$this->fluxControler->toogleHeritageAction();
	}


	
}