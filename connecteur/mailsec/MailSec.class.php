<?php
class MailSec extends Connecteur {

	const TITRE_REPLACEMENT_REGEXP = "#%TITRE%#";
	const ENTITE_REPLACEMENT_REGEXP = "#%ENTITE%#";

	/**
	 * @var ZenMail
	 */
	private $zenMail;

	/**
	 * @var DocumentEmail
	 */
	private $documentEmail;

	/**
	 * @var Journal
	 */
	private $journal;

	/**
	 * @var DonneesFormulaire
	 */
	private $connecteurConfig;

	private $mailsec_content;

	/**
	 * @var EntiteSQL
	 */
	private $entiteSQL;

	public function __construct(ZenMail $zenMail,DocumentEmail $documentEmail, Journal $journal, EntiteSQL $entiteSQL){
		$this->zenMail = $zenMail;
		$this->documentEmail = $documentEmail;
		$this->journal = $journal;
		$this->entiteSQL = $entiteSQL;
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
		$this->zenMail->setEmetteur(
			$this->connecteurConfig->getWithDefault('mailsec_from_description'),
			$this->connecteurConfig->getWithDefault('mailsec_from')
		);

		$sujet =  $this->connecteurConfig->getWithDefault('mailsec_subject');
		$this->mailsec_content = $this->connecteurConfig->getWithDefault('mailsec_content');

		$docDonneesFormulaire =  $this->getDocDonneesFormulaire();
		if ($docDonneesFormulaire) {
			$titre = $docDonneesFormulaire->getTitre();
			$sujet = preg_replace(self::TITRE_REPLACEMENT_REGEXP, $titre, $sujet);
			$this->mailsec_content = preg_replace(self::TITRE_REPLACEMENT_REGEXP, $titre, $this->mailsec_content);
		}

		$connecteur_info = $this->getConnecteurInfo();
		$entite_info = $this->entiteSQL->getInfo($connecteur_info['id_e']);

		$sujet = preg_replace(self::ENTITE_REPLACEMENT_REGEXP,$entite_info['denomination'],$sujet);
		$this->mailsec_content = preg_replace(self::ENTITE_REPLACEMENT_REGEXP,$entite_info['denomination'],$this->mailsec_content);

		$this->zenMail->setSujet($sujet);
	}
	
	private function sendEmail($id_e,$id_d, $email_info){
		$link = WEBSEC_BASE . "index.php?key={$email_info['key']}";
		$message =  "{$this->mailsec_content}\n$link";
		$this->zenMail->setDestinataire($email_info['email']);
		$this->zenMail->setContenuText($message);
		$this->zenMail->send();
		$this->documentEmail->updateRenvoi($email_info['id_de']);
		$this->journal->addActionAutomatique(
			Journal::MAIL_SECURISE,
			$id_e,
			$id_d,
			'envoi',
			"Mail sécurisé envoyée à {$email_info['email']}"
		);
	}
}