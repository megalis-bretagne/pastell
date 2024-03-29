<?php
 
class Supprimer extends ActionExecutor {

	public function go(){
		$info = $this->getDocument()->getInfo($this->id_d);
		
		$this->getDonneesFormulaire()->delete();
		$this->getDocument()->delete($this->id_d);
		
		$message = "Le document « {$info['titre']} » ({$this->id_d}) a été supprimé";
		$this->getJournal()->add(Journal::DOCUMENT_ACTION,$this->id_e,$this->id_d,"suppression",$message);
		
		$this->setLastMessage($message);

		$this->redirect("/Document/list?id_e={$this->id_e}&type={$this->type}");
		return true;
	}

}