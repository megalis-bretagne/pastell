<?php
class GlaneurLocalListerRepertoires extends ActionExecutor {

	/**
	 * @return bool
	 * @throws Exception
	 */
	public function go(){
		/** @var GlaneurLocal $glaneurLocal */
        $glaneurLocal = $this->getMyConnecteur();

		$message = $glaneurLocal->listDirectories();

		$this->setLastMessage($message);
		return true;
	}
	
}