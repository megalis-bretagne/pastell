<?php
require_once( PASTELL_PATH . "/lib/action/ActionExecutor.class.php");
require_once( PASTELL_PATH . "/lib/system/Tedetis.class.php");



class TedetisEnvoie  extends ActionExecutor {

	public function go(){
		$collectviteProperties = $donneesFormulaireFactory->get($id_e,'collectivite-properties');
		
		$tedetis = new Tedetis($collectviteProperties);
		
		
		if (!  $tedetis->postActes($donneesFormulaire) ){
			$lastError->setLastError( $tedetis->getLastError());
			header("Location: detail.php?id_d=$id_d&id_e=$id_e");
			exit;
		}
		
		
		$actionCreator = new ActionCreator($sqlQuery,$journal,$id_d);
		$actionCreator->addAction($id_e,$authentification->getId(),$action,"Le document a �t� envoy� au contr�le de l�galit�");
			
		
		$lastMessage->setLastMessage("Le document a �t� envoy� au contr�le de l�galit�");
			
		header("Location: detail.php?id_d=$id_d&id_e=$id_e");
	}
}