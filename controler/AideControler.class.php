<?php 

class AideControler extends PastellControler {

	/**
	 * @throws NotFoundException
	 */
	public function indexAction(){
		$this->{'page_title'} = "Aide";
		$this->{'template_milieu'} = "AideIndex";
		$this->renderDefault();
	}

	/**
	 * @throws NotFoundException
	 */
	public function RGPDAction(){
		$this->{'page_title'} = "RGPD";
		$this->{'template_milieu'} = "AideRGPD";
		$this->renderDefault();

	}

}