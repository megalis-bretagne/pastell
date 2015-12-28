<?php


class SignatureEnvoieMock extends ConnecteurTypeActionExecutor {

	public function go(){
		$this->setLastMessage("Action réusssie !");
		return true;
	}
}