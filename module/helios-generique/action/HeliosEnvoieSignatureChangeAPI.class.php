<?php
class HeliosEnvoieSignatureChangeAPI extends ActionExecutor{
	
	public function go(){
				
		$this->getDonneesFormulaire()->setData('envoi_signature_check', $this->getDonneesFormulaire()->get('envoi_signature'));	
	}
	
	
}