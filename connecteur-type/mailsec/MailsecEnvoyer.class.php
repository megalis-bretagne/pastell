<?php

class MailsecEnvoyer extends ConnecteurTypeActionExecutor {

	/** @var  DocumentEmail */
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
		$this->documentEmail->add($this->id_d,$to,$type);
	}

	public function go(){
		$annuaireGroupe = new AnnuaireGroupe($this->getSQLQuery(),$this->id_e);

		/** @var AnnuaireRoleSQL $annuaireRoleSQL */
		$annuaireRoleSQL = $this->objectInstancier->{'AnnuaireRoleSQL'};

		$all_ancetre = $this->getEntite()->getAncetreId();

		$donneesFormulaire = $this->getDonneesFormulaire();
		$this->documentEmail = $this->objectInstancier->{'DocumentEmail'};

		foreach(array('to','cc','bcc') as $type){

			$type = $this->getMappingValue($type);

			$lesMails = $donneesFormulaire->getFieldData($type)->getMailList();
			foreach($lesMails as $mail){
				if (preg_match("/^groupe: \"(.*)\"$/u",$mail,$matches)){
					$groupe = $matches[1];
					$id_g = $annuaireGroupe->getFromNom($groupe);
					$utilisateur = $annuaireGroupe->getAllUtilisateur($id_g);
					foreach($utilisateur as $u){
						$this->add2SendEmail("".$u['description']."".' <'.$u['email'].'>',$type);
					}
				} elseif(preg_match("/^role: \"(.*)\"$/u",$mail,$matches)){
					$role = $matches[1];
					$id_r = $annuaireRoleSQL->getFromNom($this->id_e,$role);
					$utilisateur = $annuaireRoleSQL->getUtilisateur($id_r);

					foreach($utilisateur as $u){
						$this->add2SendEmail("".$u['description']."".' <'.$u['email'].'>',$type);
					}
				} elseif(preg_match('/^groupe hérité de (.*): "(.*)"$/u',$mail,$matches) || preg_match('/^groupe global: ".*"$/u',$mail)) {
					$id_g = $annuaireGroupe->getFromNomDenomination($all_ancetre,$mail);
					$utilisateur = $annuaireGroupe->getAllUtilisateur($id_g);
					foreach($utilisateur as $u){
						$this->add2SendEmail("".$u['description']."".' <'.$u['email'].'>',$type);
					}
				} elseif(preg_match('/^rôle hérité de .*: ".*"$/u',$mail,$matches) || preg_match('/^rôle global: ".*"$/u',$mail)){
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

		$this->getActionCreator()->addAction($this->id_e,$this->id_u,$this->action, "Le document a été envoyé");

		$this->setLastMessage("Le document a été envoyé au(x) personne(s) selectionnée(s)");
		return true;
	}
}