<?php

class CreationAction extends ActionExecutor {

	const ACTION_ID = 'creation';

	/**
	 * @return string
	 * @throws Exception
	 */
	public function go(){
		$this->getDocumentEntite()->addRole($this->id_d, $this->id_e, "editeur");
		$this->setDefaultValue();
		$this->addActionOK("Création du document");
		$this->notify($this->action,$this->type,"Création du document");
		return true;
	}

	private function setDefaultValue(){
		/** @var Field $field */
		foreach($this->getDonneesFormulaire()->getFormulaire()->getFields() as $field){
			if ($field->getDefault()){
				$this->getDonneesFormulaire()->setData($field->getName(),$field->getDefault());
			}
		}
	}

}