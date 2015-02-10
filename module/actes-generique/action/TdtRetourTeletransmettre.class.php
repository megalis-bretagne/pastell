<?php
class TdtRetourTeletransmettre extends ActionExecutor {

	public function go(){
		$recuperateur = new Recuperateur($_GET);
		$error = $recuperateur->get("error");
		$message = $recuperateur->get("message");
		if ($error){
			throw new Exception("Erreur sur le Tdt : " . $message);
		}
		
		$this->changeAction("send-tdt", "Le document � �t� t�l�transmis � la pr�fecture");
		$this->notify('send-tdt', $this->type,"Le document � �t� t�l�transmis � la pr�fecture");
		
		return true;
	}
}