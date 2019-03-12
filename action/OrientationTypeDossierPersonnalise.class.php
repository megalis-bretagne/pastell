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

		$last_action = $this->getDocumentActionEntite()->getLastAction($this->id_e,$this->id_d);
		try {
			$next_action = $typeDossierDefinition->getNextAction($id_t, $last_action);
			//TODO oops, on a oublié le cas ou c'était faculatif et pas coché !!
		} catch (TypeDossierException $exception){
			$message = "Impossible de sélectionner l'action suivante de $last_action : " . $exception->getMessage();
			$this->notify('fatal-error',$this->type,$message);
			$this->changeAction('fatal-error',$message);
			return false;
		}

		$message = "sélection automatique  de l'action suivante";
		$this->notify($next_action,$this->type,$message);
		$this->changeAction($next_action,$message);
		return true;
	}

}