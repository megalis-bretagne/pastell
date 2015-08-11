<?php
require_once __DIR__.'/../init.php';

class FluxControlerTest extends PastellTestCase {
	
	public function __construct(){
		parent::__construct();
		$this->getFluxControler()->setDontRedirect(true);
	}
	
	public function setUp(){
		$this->getObjectInstancier()->Authentification->Connexion('admin',1);
		parent::setUp();
	}
	
	/**
	 * @return FluxControler
	 */
	private function getFluxControler(){
		$fluxControler = new FluxControler($this->getObjectInstancier());
		$fluxControler->setDontRedirect(true);
		return $fluxControler;
	}
	
	public function reinitDatabaseOnSetup(){
		return true;
	}
	
	public function reinitFileSystemOnSetup(){
		return true;
	}
	
	public function testEditionActionAucunConnecteur(){
		$this->setExpectedException('LastErrorException');
		$this->getFluxControler()->editionAction();
	}
	
	public function testEditionAction(){
		$this->getObjectInstancier()->ConnecteurEntiteSQL->addConnecteur(1,'mailsec','mailsec','mailsec-test');
		$_GET = array("id_e"=>1,"flux"=>"mailsec","type"=>"mailsec");
		$this->expectOutputRegex("#mailsec-test#");
		$this->getFluxControler()->editionAction();
	}
	
	public function testEditionActionSelection(){
		$id_ce = $this->getObjectInstancier()->ConnecteurEntiteSQL->addConnecteur(1,'mailsec','mailsec','mailsec-test');
		$this->getObjectInstancier()->FluxEntiteSQL->addConnecteur(1,'mailsec','mailsec',$id_ce);
		$_GET = array("id_e"=>1,"flux"=>"mailsec","type"=>"mailsec");
		$this->expectOutputRegex("#checked='checked'#");
		$this->getFluxControler()->editionAction();
	}
	
	public function testEditionActionGlobale(){
		$id_ce = $this->getObjectInstancier()->ConnecteurEntiteSQL->addConnecteur(0,'horodateur-interne','horodateur','horodateur-test');
		$this->getObjectInstancier()->FluxEntiteSQL->addConnecteur(0,'global','horodateur',$id_ce);
		$_GET = array("id_e"=>0,"flux"=>"","type"=>"horodateur");
		$this->expectOutputRegex("#checked='checked'#");
		$this->getFluxControler()->editionAction();
	}
	
	public function testDoEditionModif(){
		$id_ce = $this->getObjectInstancier()->ConnecteurEntiteSQL->addConnecteur(1,'mailsec','mailsec','mailsec-test');
		$_POST = array("id_e"=>1,"flux"=>'mailsec','type'=>'mailsec','id_ce'=>$id_ce);
		$this->setExpectedException('LastMessageException');
	 	$this->getFluxControler()->doEditionModif();
		$this->assertEquals($id_ce,$this->getObjectInstancier()->FluxEntiteSQL->getConnecteurId(1,'mailsec','mailsec'));
	}
	
	public function testDoEditionModifBadType(){
		$id_ce = $this->getObjectInstancier()->ConnecteurEntiteSQL->addConnecteur(1,'mailsec','mailsec','mailsec-test');
		$_POST = array("id_e"=>1,"flux"=>'actes-generique','type'=>'signature','id_ce'=>$id_ce);
		$this->setExpectedException('LastErrorException');
		$this->getFluxControler()->doEditionModif();
		$this->assertNotEquals($id_ce,$this->getObjectInstancier()->FluxEntiteSQL->getConnecteurId(1,'mailsec','mailsec'));
	}
	
/*	public function testDoEditionDelete(){
		$_POST = array("id_e"=>1,"flux"=>'actes-generique','type'=>'signature','id_ce' => 0);
		$this->setExpectedException('LastMessageException');
		$this->getFluxControler()->doEditionModif();
		$this->assertNull($this->getObjectInstancier()->FluxEntiteSQL->getConnecteurId(1,'actes-generique','signature'));
	}*/
	
}