<?php 

class EnvoyerMailSec extends ActionExecutor {
	
	private $documentEmail;
	
	/**
	 * @return MailSec
	 */
	private function getMailSecConnecteur(){
		return $this->getConnecteur('mailsec');
	}
	
	private function sendAllEmail(){
		$this->getMailSecConnecteur()->sendAllMail($this->id_e, $this->id_d);
	}
	
	private function add2SendEmail($to,$type){
		if ($this->documentEmail->getKey($this->id_d,$to)){
			return;
		}
		$key = $this->documentEmail->add($this->id_d,$to,$type);
	}
	
	public function go(){
		$annuaireGroupe = new AnnuaireGroupe($this->getSQLQuery(),$this->id_e);
		
		$annuaireRoleSQL = $this->objectInstancier->AnnuaireRoleSQL;
		
		$all_ancetre = $this->getEntite()->getAncetreId();
				
		$donneesFormulaire = $this->getDonneesFormulaire();
		$this->documentEmail = $this->objectInstancier->DocumentEmail;
		
		foreach(array('to','cc','bcc') as $type){
			$lesMails = $donneesFormulaire->getFieldData($type)->getMailList();
			foreach($lesMails as $mail){
				if (preg_match("/^groupe: \"(.*)\"$/",$mail,$matches)){
					$groupe = $matches[1];
					$id_g = $annuaireGroupe->getFromNom($groupe);
					$utilisateur = $annuaireGroupe->getAllUtilisateur($id_g);
					foreach($utilisateur as $u){
						$this->add2SendEmail("".$u['description']."".' <'.$u['email'].'>',$type);
					}
				} elseif(preg_match("/^role: \"(.*)\"$/",$mail,$matches)){
					$role = $matches[1];
					$id_r = $annuaireRoleSQL->getFromNom($this->id_e,$role);
					$utilisateur = $annuaireRoleSQL->getUtilisateur($id_r);
					
					foreach($utilisateur as $u){
						$this->add2SendEmail("".$u['description']."".' <'.$u['email'].'>',$type);
					}
				} elseif(preg_match('/^groupe h�rit� de (.*): "(.*)"$/',$mail,$matches) || preg_match('/^groupe global: ".*"$/',$mail)) {
					$id_g = $annuaireGroupe->getFromNomDenomination($all_ancetre,$mail);
					$utilisateur = $annuaireGroupe->getAllUtilisateur($id_g);
					foreach($utilisateur as $u){
						$this->add2SendEmail("".$u['description']."".' <'.$u['email'].'>',$type);
					}
				} elseif(preg_match('/^r�le h�rit� de .*: ".*"$/',$mail,$matches) || preg_match('/^r�le global: ".*"$/',$mail)){
					$id_r = $annuaireRoleSQL->getFromNomDenomination($all_ancetre,$mail);
					$utilisateur = $annuaireRoleSQL->getUtilisateur($id_r);
					foreach($utilisateur as $u){
						$this->add2SendEmail("".$u['description']."".' <'.$u['email'].'>',$type);
					}
					
				} else {
					$this->add2SendEmail($mail,$type);
				}
			}
		}
		
		$this->sendAllEmail();
		
		$this->getActionCreator()->addAction($this->id_e,$this->id_u,'envoi', "Le document a �t� envoy�");
		
		$this->setLastMessage("Le document a �t� envoy� au(x) personne(s) selectionn�e(s)");
		return true;		
	}
}