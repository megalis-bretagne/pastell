<?php 
class SendAE extends ActionExecutor {
	
	public function go(){
		$message = "AE envoy�; dossier termin�";
		$this->getActionCreator()->addAction($this->id_e,$this->id_u,"termine",$message);
		return true;
	}
	
}

