<?php 

class TestBordereau extends ActionExecutor {
	
	public function go(){
		$archivesSEDA = $this->getMyConnecteur();
		
		
		$transactionsInfo = array(
			'numero_acte_collectivite' => mt_rand(),
			'subject' => 'Fichier & erreur',
			'decision_date' => date("Y-m-d"),
			'latest_date' => date("Y-m-d"),
			'nature_descr' => 'bla',
			'nature_code' => 'bla & éaa <toto aa="bb"></toto>',
			'classification' => '3.1',
			'actes_file' => __DIR__ ."/../fixtures/vide.pdf",
			'ar_actes' => __DIR__."/../fixtures/ar-actes.xml",
			'annexe' => array(),
			'echange_prefecture'=>array(),
			'echange_prefecture_ar' => array(),
			'echange_prefecture_type' => array(),
		);
		
		$bordereau = $archivesSEDA->getBordereau($transactionsInfo);
		if($this->from_api){
			$this->setLastMessage($bordereau);
			return true;
		}	
		
		header("Content-type: text/xml");
		header("Content-disposition: inline; filename=bordereau.xml");
		echo $bordereau;
		exit;
	}
	
}