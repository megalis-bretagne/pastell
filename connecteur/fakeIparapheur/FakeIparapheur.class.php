<?php
class FakeIparapheur extends SignatureConnecteur {
	
	private $retour;
	private $iparapheur_type;
	private $iparapheur_envoi_status;
	private $iparapheur_temps_reponse;
	
	public function setConnecteurConfig(DonneesFormulaire $collectiviteProperties){
		$this->retour = $collectiviteProperties->get('iparapheur_retour');
		$this->iparapheur_type = $collectiviteProperties->get('iparapheur_type');
		$this->iparapheur_envoi_status = $collectiviteProperties->get('iparapheur_envoi_status');
		$this->iparapheur_temps_reponse = intval($collectiviteProperties->get('iparapheur_temps_reponse'));
	}
	
	public function getNbJourMaxInConnecteur(){
		return 30;
	}
	
	public function getSousType(){
		switch($this->iparapheur_type){
			case 'Actes':
				return array("Arrêté individuel","Arrêté réglementaire","Contrat et convention","Délibération");
			case 'PES':
				return array("BJ","Bordereau depense");
			case 'Document':
				return array("Courrier","Commande","Facture");
		}
		return array();
	}
	
	public function getDossierID($id,$name){
		$name = preg_replace("#[^a-zA-Z0-9_ ]#", "_", $name);
		return "$id $name";
	}

    /**
     * @param FileToSign $dossier
     * @return string
     * @throws Exception
     */
    public function sendDossier(FileToSign $dossier)
    {
        if ($this->iparapheur_envoi_status == 'error'){
            throw new Exception("Erreur déclenchée par le connecteur fake Iparapheur (iparapheur_envoi_status configuré à 'error')");
        }
        return "Dossier déposé pour signature";
    }

    /**
     * @deprecated 3.0
     */
	public function sendDocument($typeTechnique,$sousType,$dossierID,$document_content,$content_type,array $all_annexes = array(),$date_limite=false,$visuel_pdf=''){
		if ($this->iparapheur_envoi_status == 'error'){
			throw new Exception("Erreur déclenchée par le connecteur fake Iparapheur (iparapheur_envoi_status configuré à 'error')");
		}
		return "Dossier déposé pour signature";
	}
	
	public function getHistorique($dossierID){
		if ($this->retour == 'Fatal'){
			trigger_error("Fatal error", E_USER_ERROR);
		}
		sleep($this->iparapheur_temps_reponse);
		$date = date("d/m/Y H:i:s");
		if( $this->retour == 'Archive' ) {
			return $date . " : [Archive] Dossier signé (simulation de parapheur)!";
		}
		if( $this->retour == 'Rejet' ) {
			return $date . " : [RejetVisa] Dossier rejeté (simulation parapheur)!";
		}
		
		throw new Exception("Erreur provoquée par le simulateur du iParapheur");
	}
	
	public function getSignature($dossierID){
		$info['signature'] = "Test Signature";
		$info['document'] = "Document";
		$info['nom_document'] = "document.txt";
		$info['document_signe'] = [
			'document' => "content",
			'nom_document'=>"document_signe.txt"
		];
		$info['is_pes'] = false;
		return $info;
	}

    public function sendHeliosDocument($typeTechnique,$sousType,$dossierID,$document_content,$content_type,$visuel_pdf,	array $metadata = array()){
        return true;
    }
	
	public function getAllHistoriqueInfo($dossierID){
		if ($this->retour == 'Fatal'){
			trigger_error("Fatal error", E_USER_ERROR);
		}
		sleep($this->iparapheur_temps_reponse);
		$date = date("d/m/Y H:i:s");
		if( $this->retour == 'Archive' ) {
			return [$date . " : [Archive] Dossier signé (simulation de parapheur)!"];
		}
		if( $this->retour == 'Rejet' ) {
			return [$date . " : [RejetVisa] Dossier rejeté (simulation parapheur)!"];
		}

		throw new Exception("Erreur provoquée par le simulateur du iParapheur");
	}
	
	public function getLastHistorique($dossierID){
		
		if( $this->retour == 'Archive' ) {
			return "[Archive]";
		}
		return "[RejetVisa]";
		
	}
	
	public function effacerDossierRejete($dossierID){
		return true;
	}

	public function getLogin(){
		return "ok";
	}

    public function isFinalState(string $lastState): bool
    {
        return strstr($lastState, '[Archive]');
    }

    public function isRejected(string $lastState): bool
    {
        return strstr($lastState, '[RejetVisa]') || strstr($lastState, '[RejetSignataire]');
    }
}