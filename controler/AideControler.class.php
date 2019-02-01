<?php 

class AideControler extends PastellControler {

    public function _beforeAction() {
        parent::_beforeAction();
        $this->{'pages_without_left_menu'} = true;
    }

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

	/**
	 * @throws NotFoundException
	 */
	public function AProposAction(){
		$this->{'page_title'} = "À propos";
		$this->{'template_milieu'} = "AideAPropos";
		$text = file_get_contents(__DIR__."/../CHANGELOG.md");
		$parsedown = new Parsedown();
		$text = $parsedown->parse($text);

		$text = preg_replace("/<h2>/","<h3>",$text);
		$this->{'changelog'} = preg_replace("/<h1>/","<h2>",$text);


		$this->{'manifest_info'}= $this->getManifestFactory()->getPastellManifest()->getInfo();

		$this->renderDefault();
	}

}