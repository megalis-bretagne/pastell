<?php

class S2lowRedirectToMajCertif extends  ActionExecutor {

	public function go(){
		$this->redirect("/Connecteur/externalData?id_ce={$this->id_ce}&field=changement_certificat");
	}

}