<?php
class APIControler extends PastellControler {
	
	public function indexAction(){
		$this->page_title = "API Pastell";
		$this->template_milieu = "APIIndex"; 
		$this->renderDefault();
	}



}