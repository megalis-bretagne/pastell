<?php

class PDFGeneriqueSendGED extends ActionExecutor  {


	public function go(){

        $this->getGedConnecteur()->send($this->getDonneesFormulaire());
		$titre = $this->getDonneesFormulaire()->getTitre();
		$message = "Le document {$titre} a été versé sur le dépôt";

		$this->setLastMessage($message);
		$this->notify($this->action, $this->type,$message);
		$this->addActionOK($message);
		return true;
	}

	private function getGedConnecteur(){
		/** @var GEDConnecteur $connecteur */
		$connecteur = $this->getConnecteur("GED");
		return $connecteur;
	}

}