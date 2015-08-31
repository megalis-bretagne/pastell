<?php

class ConnecteurException extends Exception {}

abstract class Connecteur {
	
	protected $lastError;
    /**
     * @var DonneesFormulaire
     */
	private $docDonneesFormulaire;
	private $connecteurInfo;

	abstract function setConnecteurConfig(DonneesFormulaire $donneesFormulaire);

	public function getLastError(){
		return $this->lastError;
	}
	
	/**
	 * @return DonneesFormulaire
	 * Retourne les donn�es du flux en cours de traitement.
	 * Le connecteur ne doit acc�der qu'aux seuls attributs � sa port�e :
	 * - attributs publics : d�clar�s dans le flux
	 * - attributs priv�s : d�clar�s par le connecteur lui-m�me
	 * Il ne doit pas acc�der aux attributs d�clar�s par d'autres connecteurs.
	 */
	public function getDocDonneesFormulaire() {
		return $this->docDonneesFormulaire;
	}
	
	public function setDocDonneesFormulaire(DonneesFormulaire $docDonneesFormulaire) {
		$this->docDonneesFormulaire = $docDonneesFormulaire;
	}
	
	/**
	 * @return array information sur le connecteur (id_ce, id_e,...)
	 */
	public function getConnecteurInfo(){
		return $this->connecteurInfo;
	}
	
	public function setConnecteurInfo(array $connecteur_info){
		$this->connecteurInfo = $connecteur_info;
	}
	
	public function isGlobal() {
		return $this->connecteurInfo['id_e'] == 0;
	}
	
}