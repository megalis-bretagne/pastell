<?php

class UtilisateurListTest extends PastellTestCase {

	/**
	 * @var UtilisateurListe
	 */
	private $utilisateurListe;

	protected function setUp(){
		parent::setUp();
		$this->utilisateurListe = new UtilisateurListe($this->getSQLQuery());
	}

	public function testGetUtilisateurByLogin() {
		$this->assertEquals(1,$this->utilisateurListe->getUtilisateurByLogin('admin'));
	}

	public function testGtAllUtilisateurSimple(){
		$all = $this->utilisateurListe->getAllUtilisateurSimple();
		$this->assertEquals(1,$all[0]['id_u']);
	}


}