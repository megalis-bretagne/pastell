<?php

class ActionCreatorSQLTest extends PastellTestCase {

	use DocumentTestCreator;

	/**
	 * @throws Exception
	 */
	public function testAddAction(){
		$actionCreatorSQL = $this->getObjectInstancier()->getInstance(ActionCreatorSQL::class);
		$id_d = $this->createTestDocument();

		$actionCreatorSQL->addAction(1,0,"action-test","test",$id_d);

		$documentActionEntite = $this->getObjectInstancier()->getInstance(DocumentActionEntite::class);

		$this->assertEquals(
			"action-test",
			$documentActionEntite->getLastAction(1,$id_d)
		);

	}

}