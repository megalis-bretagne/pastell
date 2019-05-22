<?php

class GEDEnvoyer extends ConnecteurTypeActionExecutor {

	/**
	 * @return bool
	 * @throws Exception
	 */
	public function go(){
		$action_for_unrecoverable_error = $this->getMappingValue(FatalError::ACTION_ID);

		$donneesFormulaire = $this->getDonneesFormulaire();
		/** @var GEDConnecteur $ged */
		$ged = $this->getConnecteur("GED");

		try {
			$ged->send($donneesFormulaire);
		} catch (UnrecoverableException $e){
			$this->changeAction($action_for_unrecoverable_error,$e->getMessage());
			$this->notify(
				$action_for_unrecoverable_error,
				$this->type,
				"Erreur lors du dépot: " .$e->getMessage()
			);
			return false;
		} catch (RecoverableException $e){
			$this->setLastMessage($e->getMessage());
			return false;
		}

		$message = sprintf(
			"Le document %s a été versé sur le dépôt",
			$this->getDonneesFormulaire()->getTitre()
		);

		$this->addActionOK($message);
		$this->notify($this->action, $this->type,$message);

		return true;
	}

}