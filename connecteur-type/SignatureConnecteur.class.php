<?php
abstract class SignatureConnecteur extends Connecteur {
	
		abstract public function getNbJourMaxInConnecteur();
		
		abstract public function getSousType();
	
		abstract public function getDossierID($id,$name);
		
		abstract function sendDocument($typeTechnique,$sousType,$dossierID,$document_content,$content_type,array $all_annexes = array(),$date_limite=false,$visuel_pdf='');
	
		abstract public function getHistorique($dossierID);
	
		abstract public function getSignature($dossierID);

        abstract public function sendHeliosDocument($typeTechnique,$sousType,$dossierID,$document_content,$content_type,$visuel_pdf,array $metadata = array());
	
		abstract function getAllHistoriqueInfo($dossierID);
		
		abstract public function getLastHistorique($dossierID);
	
		abstract public function effacerDossierRejete($dossierID);
		
		public function hasTypeSousType(){
			return true;
		}	
		
		/**
		 * Indique si le connecteur est un connecteur de signature "locale", c'est à dire par applet sur le navigateur et sans appel à un serveur de signature externe
		 * @return boolean
		 */
		public function isLocalSignature(){
			return false;	
		}

		public function setSendingMetadata(DonneesFormulaire $donneesFormulaire){/*Nothing to do*/}

        public function archiver($dossierID){return true;}

		public function getOutputAnnexe(array $info_from_get_signature,int $ignore_count){return [];}

}