<?php

class StandardAction extends ActionExecutor {

	public function go(){
		$documentType = $this->getDocumentType();
		$connecteur_type = $documentType->getAction()->getProperties($this->action,'connecteur-type');
		if (! $connecteur_type){
			throw new RecoverableException("Aucun connecteur type n'a été défini pour l'action {$this->action}");
		}

		$connecteur_type_action = $documentType->getAction()->getProperties($this->action,'connecteur-type-action');
		if(! $connecteur_type_action){
			throw new RecoverableException("Aucune action n'a été défini pour l'action {$this->action} (connecteur-type : $connecteur_type)");
		}

		/** @var ConnecteurTypeFactory $connecteurTypeFactory */
		$connecteurTypeFactory = $this->objectInstancier->{'ConnecteurTypeFactory'};
		$connecteurTypeActionExecutor = $connecteurTypeFactory->getActionExecutor($connecteur_type,$connecteur_type_action);

		if (! $connecteurTypeActionExecutor){
			throw new RecoverableException("Impossible d'instancier une classe pour l'action : $connecteur_type:$connecteur_type_action");
		}

		$connecteurTypeActionExecutor->setEntiteId($this->id_e);
		$connecteurTypeActionExecutor->setUtilisateurId($this->id_u);
		$connecteurTypeActionExecutor->setAction($this->action);

		$connecteurTypeActionExecutor->setDocumentId($this->type,$this->id_d);
		$connecteurTypeActionExecutor->setDestinataireId($this->id_destinataire?:array());
		$connecteurTypeActionExecutor->setActionParams($this->action_params?:array());
		$connecteurTypeActionExecutor->setFromApi($this->from_api);
		$connecteurTypeActionExecutor->setIdWorker($this->id_worker);

		$connecteur_type_mapping = $documentType->getAction()->getProperties($this->action,'connecteur-type-mapping');
		if (! $connecteur_type_mapping){
			$connecteur_type_mapping = array();
		}
		$connecteurTypeActionExecutor->setMapping($connecteur_type_mapping);

        $connecteur_type_data_seda_class_name = $documentType->getAction()->getConnecteurTypeDataSedaClassName($this->action);
        if (! $connecteur_type_data_seda_class_name){
            $connecteur_type_data_seda_class_name = "FluxDataStandard";
        }
        $connecteurTypeActionExecutor->setDataSedaClassName($connecteur_type_data_seda_class_name);

		$result = $connecteurTypeActionExecutor->go();
		$this->setLastMessage($connecteurTypeActionExecutor->getLastMessage());

		return $result;
	}

}