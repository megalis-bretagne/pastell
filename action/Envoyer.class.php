<?php
require_once( PASTELL_PATH . "/lib/action/ActionExecutor.class.php");

class Envoyer extends ActionExecutor {

	
	public function go(){
		
		if ( ! $this->destinataire ){
			$this->setLastMessage("Vous devez selectionner un destinataire");
			return false;
		}
		
		$infoEntite = $this->getEntite()->getInfo();
		$emmeteurName = $infoEntite['denomination'];
		
		
		foreach($this->destinataire  as $id_col) {
			
			$this->getDocumentEntite()->addRole($this->id_d,$id_col,"lecteur");
			
			$entiteCollectivite = new Entite($this->getSQLQuery(),$id_col);
			$infoCollectivite = $entiteCollectivite->getInfo();
			$denomination_col = $infoCollectivite['denomination']; 			
			
			$this->getActionCreator()->addAction($this->id_e,$this->id_u,'envoi', "Le document a �t� envoy�  � $denomination_col");
			$this->getActionCreator()->addToEntite($id_col,"Le document a �t� envoy� par $emmeteurName");
			
			$this->getActionCreator()->addAction($id_col,0,'recu', "Le document a �t� re�u ");
			$this->getActionCreator()->addToEntite($this->id_e,"Le document a �t� re�u par $denomination_col");
			
			$this->getNotificationMail()->notify($id_col,$this->id_d, $this->action, $this->type,"Vous avez un nouveau message");		
		}
		
		$this->setLastMessage("Le document a �t� envoy� au(x) entit�(s) selectionn�e(s)");
		return true;		
	}
}