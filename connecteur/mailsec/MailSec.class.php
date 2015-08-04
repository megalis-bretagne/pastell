<?php
class MailSec extends Connecteur {
	
	private $zenMail;
	private $documentEmail;
	private $journal;
	private $connecteurConfig;
	
	public function __construct(ZenMail $zenMail,DocumentEmail $documentEmail, Journal $journal){
		$this->zenMail = $zenMail;
		$this->documentEmail = $documentEmail;
		$this->journal = $journal;
	}
	
	public function setConnecteurConfig(DonneesFormulaire $connecteurConfig){
		$this->connecteurConfig = $connecteurConfig; 
	}
	
	public function sendAllMail($id_e,$id_d){
		$this->configZenMail();
		foreach($this->documentEmail->getInfo($id_d) as $email_info){
			$this->sendEmail($id_e,$id_d,$email_info);
		}
	}
	
	public function sendOneMail($id_e,$id_d,$id_de){
		$this->configZenMail();
		$email_info = $this->documentEmail->getInfoFromPK($id_de);
		$this->sendEmail($id_e,$id_d,$email_info);
	}
	
	private function configZenMail(){
		$this->zenMail->setEmetteur($this->connecteurConfig->getWithDefault('mailsec_from_description'),$this->connecteurConfig->getWithDefault('mailsec_from'));
		$this->zenMail->setSujet($this->connecteurConfig->getWithDefault('mailsec_subject'));
	}
	
	private function sendEmail($id_e,$id_d, $email_info){
		$link = WEBSEC_BASE . "index.php?key={$email_info['key']}";
		$message = $this->connecteurConfig->getWithDefault('mailsec_content') . "\n" . $link;
		$this->zenMail->setDestinataire($email_info['email']);
		$this->zenMail->setContenuText($message);
		$this->zenMail->send();
		$this->documentEmail->updateRenvoi($email_info['id_de']);
		$this->journal->addActionAutomatique(Journal::MAIL_SECURISE,$id_e,$id_d,'envoi',"Mail sécurisé envoyée à {$email_info['email']}");
	}
}