<?php

class FakeSEDA extends SEDAConnecteur {

	public function setConnecteurConfig(DonneesFormulaire $donneesFormulaire) {
		/* Nothing to do */
	}

	public function getBordereau(array $transactionsInfo) {
		return file_get_contents(__DIR__."/fixtures/bordereau.xml");
	}

}