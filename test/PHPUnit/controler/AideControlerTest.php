<?php


class AideControlerTest extends ControlerTestCase {

	/** @var  AideControler */
	private $aideControler;

	protected function setUp() {
		parent::setUp();
		$this->aideControler = $this->getControlerInstance("AideControler");
	}

	/**
	 * @throws NotFoundException
	 */
	public function testIndex(){
		$this->expectOutputRegex("##");
		$this->aideControler->indexAction();
	}

	public function testApropos(){
	    $this->expectOutputRegex("#Journal des modifications#");
	    $this->aideControler->AProposAction();
    }

}