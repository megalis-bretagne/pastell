<?php
require_once( PASTELL_PATH . "/lib/action/ActionExecutor.class.php");
require_once( PASTELL_PATH . "/lib/system/Tedetis.class.php");



class TedetisEnvoie  extends ActionExecutor {

	public function go(){
		$collectiviteProperties = $this->getDonneesFormulaireFactory()->get($this->id_e,'collectivite-properties');
		
		
		$tedetis = new Tedetis($collectiviteProperties);
		
		if (!  $tedetis->postActes($this->getDonneesFormulaire()) ){
			$this->setLastMessage( $tedetis->getLastError());
			return false;
		}
		
		$this->getActionCreator()->addAction($this->id_e,$this->id_u,$this->action,"Le document a �t� envoy� au contr�le de l�galit�");
			
		
		$this->setLastMessage("Le document a �t� envoy� au contr�le de l�galit�");
		return true;			
	}
}