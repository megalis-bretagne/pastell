<?php

class ConnecteurFrequenceTest extends LegacyPHPUnit_Framework_TestCase {

	public function testConstruct(){
		$connecteurFrequence = new ConnecteurFrequence(array('type_connecteur'=>'toto','id_cf'=>12));
		$this->assertEquals('toto',$connecteurFrequence->type_connecteur);
	}

	public function testGetArray(){
		$connecteurFrequence = new ConnecteurFrequence(array('type_connecteur'=>'toto','id_cf'=>12));
		$this->assertEquals('toto',$connecteurFrequence->getArray()['type_connecteur']);
	}

	public function testGetgetAttributeName(){
		$connecteurFrequence = new ConnecteurFrequence();
		$result = $connecteurFrequence->getAttributeName();
		$this->assertEquals(9,count($result));
		$this->assertEquals('id_verrou',$result[8]);
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
		$this->assertEquals("(Document) Tous les types de dossiers",$connecteurFrequence->getActionSelector());
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

	private function assertFrequence($frequence_in_minute,$date){
		$expected_time = strtotime("+$frequence_in_minute minute");
		if ($expected_time - strtotime($date) > 1){
			throw new Exception("Failed that $date is ".date("Y-m-d H:i:s",$expected_time));
		}
        $this->assertTrue(true);
	}

	public function testGetNextTryEmpty(){
		$connecteurFrequence = new ConnecteurFrequence();
		$this->assertEquals('',$connecteurFrequence->getNextTry(42));
	}

	public function testGetNextTry(){
		$frequence_in_minute = 5;
		$connecteurFrequence = new ConnecteurFrequence();
		$connecteurFrequence->expression="$frequence_in_minute";
		$date = $connecteurFrequence->getNextTry(1);
		$this->assertFrequence($frequence_in_minute,$date);
	}

	public function testGetNextTryFrequence(){
		$connecteurFrequence = new ConnecteurFrequence();
		$connecteurFrequence->expression="1X5\n60";
		$date = $connecteurFrequence->getNextTry(1);
		$this->assertFrequence(1,$date);
	}

	public function testGetNextTryFrequenceLoin(){
		$connecteurFrequence = new ConnecteurFrequence();
		$connecteurFrequence->expression="1X5\n60";
		$date = $connecteurFrequence->getNextTry(10);
		$this->assertFrequence(60,$date);
	}

	public function testGetNextTryFrequenceSpace(){
		$connecteurFrequence = new ConnecteurFrequence();
		$connecteurFrequence->expression="1 X 5\n60 X 10";
		$date = $connecteurFrequence->getNextTry(10);
		$this->assertFrequence(60,$date);
	}

	/**
	 * @dataProvider frequenceProvider
	 */
	public function testGetNextTryFrequencePlusLoin($minute_expected,$nb_try) {
		$connecteurFrequence = new ConnecteurFrequence();
		$connecteurFrequence->expression = "1X5\n60X10\n1X25\n42";
		$this->assertFrequence($minute_expected, $connecteurFrequence->getNextTry($nb_try));
	}

	public function frequenceProvider() {
		return [ [1, 0], [1, 1], [1, 4], [60, 5], [60, 14], [1,15], [1,49], [42,50], [42,500]];
	}

	public function testRelativeDate(){
		$connecteurFrequence = new ConnecteurFrequence();
		$connecteurFrequence->expression = 10;
		$next = $connecteurFrequence->getNextTry(42,"2012-06-27 18:23:46");
		$this->assertEquals("2012-06-27 18:33:46",$next);
	}

	public function testFrequenceCron(){
		$connecteurFrequence = new ConnecteurFrequence();
		$connecteurFrequence->expression = "(40 2 * * *)";
		$next = $connecteurFrequence->getNextTry(42,"2017-04-13 11:48:45");
		$this->assertEquals("2017-04-14 02:40:00",$next);
	}

	public function testLastExpression(){
		$connecteurFrequence = new ConnecteurFrequence();
		$connecteurFrequence->expression = "1 X 10";
		$this->setExpectedException("Exception","Trop d'essai sur le connecteur");
		$connecteurFrequence->getNextTry(11);
	}

	public function testGetExpressionAsString(){
		$connecteurFrequence = new ConnecteurFrequence();
		$connecteurFrequence->expression = "1 X 10\n(* * * * *) X 1\n60 X 1";
		$expr = $connecteurFrequence->getExpressionAsString();
		$this->assertEquals("Toutes les minutes (10 fois)\nA (* * * * *) (1 fois)\nToutes les 60 minutes (1 fois)\nVerrouiller le travail",$expr);
	}

	public function testGetExpressionAsStringEmpty(){
		$connecteurFrequence = new ConnecteurFrequence();
		$expr = $connecteurFrequence->getExpressionAsString();
		$this->assertEquals("\n",$expr);
	}



}