<?php

class MailSecRenvoyer extends ConnecteurTypeActionExecutor {

	/**
	 * @return MailSec
	 */
	private function getMailSecConnecteur(){
		return $this->getConnecteur('mailsec');
	}

	public function go(){
		$recuperateur = new Recuperateur($_POST);
		$id_de = $recuperateur->getInt('id_de');

		if ($id_de){
			$this->setLastMessage("Un email a été renvoyé au destinataire");
			$this->getMailSecConnecteur()->sendOneMail($this->id_e,$this->id_d,$id_de);
		} else {
			$this->getMailSecConnecteur()->sendAllMail($this->id_e, $this->id_d);
			$this->setLastMessage("Un email a été renvoyé à tous les destinataires");
		}

		return true;
	}

}