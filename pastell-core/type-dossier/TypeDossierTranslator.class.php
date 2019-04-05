<?php

class TypeDossierTranslator {

	const ORIENTATION = 'orientation';

	private $ymlLoader;
	private $typeDossierEtapeDefinition;

	public function __construct(
		YMLLoader $ymlLoader,
		TypeDossierEtapeDefinition $typeDossierEtapeDefinition
	) {
		$this->ymlLoader = $ymlLoader;
		$this->typeDossierEtapeDefinition = $typeDossierEtapeDefinition;
	}

	/**
	 * Construit le YAML d'un type de dossier a partir d'un TypeDossierData
	 * @param TypeDossierData $typeDossierData
	 * @return array
	 */
	public function getDefinition(TypeDossierData $typeDossierData){
		$result = $this->setStarter($typeDossierData);
		$this->setFormulaireElement($typeDossierData,$result);
		$this->setOngletCheminement($typeDossierData,$result);
		$this->setOngletForEtapeList($typeDossierData,$result);
		$this->setPageCondition($typeDossierData,$result);
		$this->setConnecteur($typeDossierData,$result);
		$this->setAction($typeDossierData,$result);
		$this->setSpecific($typeDossierData,$result);
		return $result;
	}

	private function setStarter(TypeDossierData $typeDossierData){
		$result = $this->ymlLoader->getArray(__DIR__ . "/../../type-dossier/type-dossier-starter-kit.yml");
		$result[DocumentType::NOM] = $typeDossierData->nom;
		$result[DocumentType::TYPE_FLUX] = $typeDossierData->type;
		$result[DocumentType::DESCRIPTION] = $typeDossierData->description;
		$result[DocumentType::FORMULAIRE] = [];
		$result[DocumentType::PAGE_CONDITION] = $result[DocumentType::PAGE_CONDITION]?:[];
		$result[DocumentType::CONNECTEUR] = $result[DocumentType::CONNECTEUR]?:[];
		return $result;
	}

	private function setFormulaireElement(TypeDossierData $typeDossierData, array & $result){
		$onglet_name = $typeDossierData->nom_onglet?:'onglet1';
		foreach($typeDossierData->formulaireElement as $element_id => $typeDossierFormulaireElement){
			$result[DocumentType::FORMULAIRE][$onglet_name][$element_id] = [
				'name' => $typeDossierFormulaireElement->name?:$element_id,
				'type' => $this->getType($typeDossierFormulaireElement),
				Field::REQUIS=> boolval($typeDossierFormulaireElement->requis),
				'multiple' => boolval($typeDossierFormulaireElement->type == 'multi_file'),
				'commentaire' => $typeDossierFormulaireElement->commentaire
			];
			if ($typeDossierFormulaireElement->titre){
				$result[DocumentType::FORMULAIRE][$onglet_name][$element_id]['title'] = true;
			}
			if ($typeDossierFormulaireElement->champs_recherche_avancee || $typeDossierFormulaireElement->champs_affiches){
				$result[DocumentType::FORMULAIRE][$onglet_name][$element_id]['index'] = true;
			}
			if ($typeDossierFormulaireElement->champs_affiches){
				$result['champs-affiches'][] = $element_id;
			}
			if ($typeDossierFormulaireElement->champs_recherche_avancee){
				$result['champs-recherche-avancee'][] = $element_id;
			}
		}
	}

	private function setOngletCheminement(TypeDossierData $typeDossierData, array & $result){
		$cheminement = [];
		$has_cheminement_onglet = false;
		foreach($typeDossierData->etape as $typeDossierEtape) {
			if (! $typeDossierEtape->requis){
				$has_cheminement_onglet = true;
			}
			$cheminement[] = $typeDossierEtape;
		}
		if ($has_cheminement_onglet) {
			foreach ($cheminement as $typeDossierEtape) {

                $element_id = $this->getEnvoiTypeElementId($typeDossierEtape);
				$result[DocumentType::FORMULAIRE]['Cheminement'][$element_id] =
					[
						'name' => $this->getEnvoiTypeLibelle($typeDossierEtape),
						'type' => 'checkbox',
						'onchange' => 'cheminement-change',
						'default' => $typeDossierEtape->requis?"checked":"",
						'read-only' => boolval($typeDossierEtape->requis)
					];
			}
		}
	}

	private function getEnvoiTypeElementId(TypeDossierEtape $typeDossierEtape) : string {
	    $result =  "envoi_{$typeDossierEtape->type}";
	    if (! $typeDossierEtape->etape_with_same_type_exists){
	        return $result;
        }

	    return sprintf("%s_%d",$result,$typeDossierEtape->num_etape_same_type+1);
    }

    private function getEnvoiTypeLibelle(TypeDossierEtape $typeDossierEtape) : string {
        $all_type = $this->typeDossierEtapeDefinition->getAllType();

        $result =  $all_type[$typeDossierEtape->type];
        if (! $typeDossierEtape->etape_with_same_type_exists){
            return $result;
        }

        return sprintf("%s #%d",$result,$typeDossierEtape->num_etape_same_type+1);
    }

	private function setOngletForEtapeList(TypeDossierData $typeDossierData, array & $result){
		foreach($typeDossierData->etape as $etape) {
			foreach ($this->typeDossierEtapeDefinition->getFormulaireForEtape($etape) as $onglet_name => $onglet_content) {
				$result[DocumentType::FORMULAIRE][$onglet_name] = $onglet_content;
			}
		}
	}

	private function getElementIdList($result){
		$element_id_list = [];
		foreach($result[DocumentType::FORMULAIRE] as $element_list){
			foreach($element_list as $element_id => $element_properties){
				$element_id_list[] = $element_id;
			}
		}
		return $element_id_list;
	}

	private function setPageCondition(TypeDossierData $typeDossierData, array & $result){
		$element_id_list = $this->getElementIdList($result);
		foreach($typeDossierData->etape as $etape) {
			foreach ($this->typeDossierEtapeDefinition->getPageCondition($etape) as $onglet_name => $onglet_condition) {
				foreach ($onglet_condition as $element_id => $element_value) {
					if (in_array($element_id, $element_id_list)) {
						$result[DocumentType::PAGE_CONDITION][$onglet_name] = $onglet_condition;
					}
				}
			}
		}

		if (! $result[DocumentType::PAGE_CONDITION]){
			unset($result[DocumentType::PAGE_CONDITION]);
		}
	}

	private function setConnecteur(TypeDossierData $typeDossierData, array & $result){
		foreach($typeDossierData->etape as  $etape) {
			$result['connecteur'] = array_merge($result['connecteur'], $this->typeDossierEtapeDefinition->getConnecteurType($etape->type));
		}
	}

	private function setAction(TypeDossierData $typeDossierData, array & $result){
		$this->setBaseAction($typeDossierData,$result);
		$this->setActionAutomatique($typeDossierData,$result);
	}

	private function setBaseAction(TypeDossierData $typeDossierData, array & $result){
		foreach($typeDossierData->etape as $etape){
			$action_list = $this->typeDossierEtapeDefinition->getActionForEtape($etape);

			foreach($action_list as $action_id => $action_properties) {
				$result[DocumentType::ACTION][$action_id] = $action_properties;
				if ($etape->etape_with_same_type_exists){
                    $result[DocumentType::ACTION][$action_id]['num-same-connecteur'] = strval($etape->num_etape_same_type);
                }
			}

		}
	}

	private function setActionAutomatique(TypeDossierData $typeDossierData, array & $result){
		foreach($typeDossierData->etape as $etape) {
			foreach ($this->typeDossierEtapeDefinition->getActionForEtape($etape) as $action_id => $action_properties) {
				if (isset($action_properties[Action::ACTION_AUTOMATIQUE]) && $action_properties[Action::ACTION_AUTOMATIQUE] == self::ORIENTATION) {
					$result['action'][self::ORIENTATION]['rule'][Action::ACTION_RULE_LAST_ACTION][] = $action_id;
					if (! $etape->automatique){
						unset($result['action'][$action_id][Action::ACTION_AUTOMATIQUE]);
					}
				}
			}
		}
	}

	private function setSpecific(TypeDossierData $typeDossierData, array & $result){
		foreach($typeDossierData->etape as $etape) {
			$result = $this->typeDossierEtapeDefinition->setSpecificData($etape, $result);
		}
	}


	private function getType(TypeDossierFormulaireElement $typeDossierFormulaireElement){
		if ($typeDossierFormulaireElement->type == 'multi_file'){
			return 'file';
		}
		return $typeDossierFormulaireElement->type;
	}

}