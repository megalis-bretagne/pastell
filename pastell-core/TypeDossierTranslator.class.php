<?php

class TypeDossierTranslator {

	public function getDefinition(TypeDossierData $typeDossierData){

		$result['nom'] = $typeDossierData->nom;;
		$result['type'] = $typeDossierData->type;
		$result['description'] = $typeDossierData->description;

		$result['formulaire'] = "";
		$result['action'] = "";



		return $result;
	}

}