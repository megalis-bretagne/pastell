<?php


class AideControlerTest extends ControlerTestCase {

	/** @var  AideControler $aideControler*/
	private $aideControler;

	protected function setUp() {
		parent::setUp();
		$this->aideControler = $this->getControlerInstance(AideControler::class);
	}

	/**
	 * @throws NotFoundException
	 */
	public function testIndex(){
		$this->expectOutputRegex("##");
		$this->aideControler->indexAction();
	}

    /**
     * @throws NotFoundException
     */
	public function testApropos(){
	    $this->expectOutputRegex("#Journal des modifications#");
	    $this->aideControler->AProposAction();
    }

    /**
     * @throws NotFoundException
     */
    public function testRGPD(){
        $this->expectOutputRegex("#<h2>RESPONSABLE DU TRAITEMENT</h2>#");
	    $this->aideControler->RGPDAction();
    }

}