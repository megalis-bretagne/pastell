<?php


class SignatureEnvoieMock extends ConnecteurTypeActionExecutor {

	public function go(){
		$this->setLastMessage("Action r�usssie !");
		return true;
	}
}