<?php

class IParapheurRedirectToMajCertif extends  ActionExecutor {

	public function go(){
		$this->redirect("/Connecteur/externalData?id_ce={$this->id_ce}&field=changement-certificat");
	}

}