<?php

class OrientationTypeDossierPersonnalise extends ActionExecutor {

	/**
	 * @throws Exception
	 */
	public function go(){

		$module_id = $this->getDocumentType()->getModuleId();

		$typeDossierSQL = $this->objectInstancier->getInstance(TypeDossierSQL::class);

		$id_t = $typeDossierSQL->getByIdTypeDossier($module_id);

		$typeDossierDefinition = $this->objectInstancier->getInstance(TypeDossierDefinition::class);

		$typeDossierData = $typeDossierDefinition->getTypeDossierData($id_t);

		$last_action = $this->getDocumentActionEntite()->getLastAction($this->id_e,$this->id_d);

		if (in_array($last_action,['creation','importation','modification'])){
			$action = $this->getActionFromEtape($typeDossierData->etape[0]);
		}
		//TODO selection de l'action suivante... algo compliqué


		$message = "sélection automatique  de l'action suivante";
		//TODO notification ?
		$this->changeAction($action,$message);

		$this->setLastMessage($message);

		return true;
	}

	/**
	 * @param TypeDossierEtape $etape
	 * @return string
	 * @throws Exception
	 */
	private function getActionFromEtape(TypeDossierEtape $etape){
		if ($etape->type == 'depot'){
			return "preparation-send-ged";
		}
		//TODO Fatal error ?
		throw new Exception("l'action n'a pas pu être trouvé");
	}
}