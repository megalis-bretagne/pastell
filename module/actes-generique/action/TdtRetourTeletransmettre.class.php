<?php
class TdtRetourTeletransmettre extends ActionExecutor {

	public function go(){
				
		$recuperateur = new Recuperateur($_GET);
		$error = $recuperateur->get("error");
		$message = $recuperateur->get("message");
		if ($error){
			throw new Exception("Erreur sur le Tdt : " . $message);
		}
		
		$tdt = $this->getConnecteur("TdT");
		
		$tedetis_transaction_id = $this->getDonneesFormulaire()->get('tedetis_transaction_id');
		
		$status =  $tdt->getStatus($tedetis_transaction_id);
		
		//A priori, c'est le seul cas que je vois ou la transaction n'a pas encore �t� post�
		if (in_array($status, array(TdtConnecteur::STATUS_ACTES_EN_ATTENTE_DE_POSTER))){
			throw new Exception("La transaction n'a pas le bon status : ".TdtConnecteur::getStatusString($status)." trouv�" ) ;
		}	
		
		$this->changeAction("send-tdt", "Le document � �t� t�l�transmis � la pr�fecture");
		$this->notify('send-tdt', $this->type,"Le document � �t� t�l�transmis � la pr�fecture");
		
		return true;
	}
}