<?php

class FluxControlerTest extends ControlerTestCase {

	/** @var  FluxControler */
	private $fluxControler;

	public function setUp(){
		parent::setUp();
		$this->fluxControler = $this->getControlerInstance("FluxControler");
	}

	public function testEditionActionAucunConnecteur(){
		$this->setExpectedException('LastErrorException');
		$this->fluxControler->editionAction();
	}
	
	public function testEditionAction(){
		$this->getObjectInstancier()->ConnecteurEntiteSQL->addConnecteur(1,'mailsec','mailsec','mailsec-test');
		$this->setGetInfo(array("id_e"=>1,"flux"=>"mailsec","type"=>"mailsec"));
		$this->expectOutputRegex("#mailsec-test#");
		$this->fluxControler->editionAction();
	}
	
	public function testEditionActionSelection(){
		$id_ce = $this->getObjectInstancier()->ConnecteurEntiteSQL->addConnecteur(1,'mailsec','mailsec','mailsec-test');
		$this->getObjectInstancier()->FluxEntiteSQL->addConnecteur(1,'mailsec','mailsec',$id_ce);
		$this->setGetInfo(array("id_e"=>1,"flux"=>"mailsec","type"=>"mailsec"));
		$this->expectOutputRegex("#checked='checked'#");
		$this->fluxControler->editionAction();
	}
	
	public function testEditionActionGlobale(){
		$id_ce = $this->getObjectInstancier()->ConnecteurEntiteSQL->addConnecteur(0,'horodateur-interne','horodateur','horodateur-test');
		$this->getObjectInstancier()->FluxEntiteSQL->addConnecteur(0,'global','horodateur',$id_ce);
		$this->setGetInfo(array("id_e"=>0,"flux"=>"","type"=>"horodateur"));
		$this->expectOutputRegex("#checked='checked'#");
		$this->fluxControler->editionAction();
	}
	
	public function testDoEditionModif(){
		$id_ce = $this->getObjectInstancier()->ConnecteurEntiteSQL->addConnecteur(1,'mailsec','mailsec','mailsec-test');
		$this->setPostInfo(array("id_e"=>1,"flux"=>'mailsec','type'=>'mailsec','id_ce'=>$id_ce));
		$this->setExpectedException('LastMessageException');
	 	$this->fluxControler->doEditionAction();
		$this->assertEquals($id_ce,$this->getObjectInstancier()->FluxEntiteSQL->getConnecteurId(1,'mailsec','mailsec'));
	}
	
	public function testDoEditionModifBadType(){
		$id_ce = $this->getObjectInstancier()->ConnecteurEntiteSQL->addConnecteur(1,'mailsec','mailsec','mailsec-test');
		$this->setPostInfo(array("id_e"=>1,"flux"=>'actes-generique','type'=>'signature','id_ce'=>$id_ce));
		$this->setExpectedException('LastErrorException');
		$this->fluxControler->doEditionAction();
		$this->assertNotEquals($id_ce,$this->getObjectInstancier()->FluxEntiteSQL->getConnecteurId(1,'mailsec','mailsec'));
	}
	
	public function testDoEditionDelete(){
		$this->setPostInfo(array("id_e"=>1,"flux"=>'actes-generique','type'=>'signature','id_ce' => 0));
		$this->setExpectedException('LastMessageException');
		$this->fluxControler->doEditionAction();
		$this->assertNull($this->getObjectInstancier()->FluxEntiteSQL->getConnecteurId(1,'actes-generique','signature'));
	}
	
	public function testGetListFlux(){
		$result = $this->fluxControler->getListFlux(1);
		$this->assertNotEmpty($result);
	}
	
	/**
	 * @expectedException Exception
	 * @expectedExceptionMessage Le type de flux n'existe pas.
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