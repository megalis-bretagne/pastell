<?php

class EntiteControlerTest extends ControlerTestCase {

	/**
	 * @var EntiteControler
	 */
	private $entiteControler;

	protected  function setUp(){
		parent::setUp();
		$this->entiteControler = $this->getControlerInstance("EntiteControler");
	}

	public function testConnecteurAction(){
		$this->expectOutputRegex("#Liste des connecteurs#");
		$this->entiteControler->connecteurAction();
		$all_connecteur = $this->entiteControler->getViewParameter();
		$this->assertEquals("horodateur-interne",$all_connecteur['all_connecteur'][0]['id_connecteur']);
	}


	public function testUtilisateurAction(){
		$this->expectOutputRegex("#Liste des utilisateurs#");
		$this->entiteControler->utilisateurAction();
		$utilisateur_list = $this->entiteControler->getViewParameter()['liste_utilisateur'];
		$this->assertEquals('Pommateau',$utilisateur_list[0]['nom']);
	}

	public function testDetailEntite(){
		$this->expectOutputRegex("#Informations - Pastell#");

		$this->setGetInfo(array('id_e' => 1));
		$this->entiteControler->detailEntite();
		$info = $this->entiteControler->getViewParameter()['entiteExtendedInfo'];
		$this->assertEquals('Bourg-en-Bresse',$info['denomination']);
	}
}