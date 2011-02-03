<?php
require_once( PASTELL_PATH . "/lib/action/ActionExecutor.class.php");
require_once( PASTELL_PATH . "/lib/system/Tedetis.class.php");



class TedetisEnvoie  extends ActionExecutor {

	public function go(){
		
		/*$id_e_col = $this->getEntite()->getCollectiviteAncetre();
				
		$collectiviteProperties = $this->getDonneesFormulaireFactory()->get($id_e_col,'collectivite-properties');
		*/
		
		
		$tedetis = new Tedetis($this->getCollectiviteProperties());
		
		if (!  $tedetis->postActes($this->getDonneesFormulaire()) ){
			$this->setLastMessage( $tedetis->getLastError());
			return false;
		}
		
		$this->getActionCreator()->addAction($this->id_e,$this->id_u,$this->action,"Le document a �t� envoy� au contr�le de l�galit�");
			
		
		$this->setLastMessage("Le document a �t� envoy� au contr�le de l�galit�");
		return true;			
	}
}