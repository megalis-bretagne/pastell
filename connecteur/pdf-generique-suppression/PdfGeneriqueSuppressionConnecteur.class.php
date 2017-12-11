<?php

class PdfGeneriqueSuppressionConnecteur extends Connecteur {

	/** @var  DonneesFormulaire */
	private $connecteurConfig;

	public function setConnecteurConfig(DonneesFormulaire $donneesFormulaire) {
		$this->connecteurConfig = $donneesFormulaire;
	}

	public function canDelete($last_change){
		return (time() - strtotime($last_change)) > $this->connecteurConfig->get('nb_day') * 86400;
	}

}