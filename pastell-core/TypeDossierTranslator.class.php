<?php

class TypeDossierTranslator {

	public function getDefinition($info){

		$result['nom'] = $info['nom'];
		$result['type'] = $info['type'];
		$result['description'] = $info['description'];

		$result['formulaire'] = "";
		$result['action'] = "";



		return $result;
	}

}