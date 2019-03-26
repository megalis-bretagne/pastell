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
		foreach($typeDossierData->etape as $etape) {
			if (! $etape->requis){
				$has_cheminement_onglet = true;
			}
			$cheminement[$etape->type] = [
				'libelle'=> $this->typeDossierEtapeDefinition->getAllType()[$etape->type],
				Field::REQUIS => $etape->requis
			];
		}
		if ($has_cheminement_onglet) {
			foreach ($cheminement as $etape_id => $etape_info) {
				$result[DocumentType::FORMULAIRE]['Cheminement']["envoi_$etape_id"] =
					[
						'name' => $etape_info['libelle'],
						'type' => 'checkbox',
						'onchange' => 'cheminement-change',
						'default' => $etape_info['requis']?"checked":"",
						'read-only' => boolval($etape_info['requis'])
					];
			}
		}
	}

	private function setOngletForEtapeList(TypeDossierData $typeDossierData, array & $result){
		foreach($typeDossierData->etape as $etape) {
			foreach ($this->typeDossierEtapeDefinition->getFormulaire($etape->type) as $onglet_name => $onglet_content) {
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
			foreach ($this->typeDossierEtapeDefinition->getPageCondition($etape->type) as $onglet_name => $onglet_condition) {
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
		$this->setActionAutomatiqueProperties($typeDossierData,$result);
		$this->setActionAutomatique($typeDossierData,$result);
		$this->setLastActionProperties($typeDossierData,$result);
	}

	private function setActionAutomatiqueProperties(TypeDossierData $typeDossierData, array & $result){
		foreach($typeDossierData->etape as $num_etape => $etape){
			$action_list = $this->typeDossierEtapeDefinition->getAction($etape->type);

			foreach($action_list as $action_id => $action_properties) {
				if (isset($action_properties[Action::ACTION_AUTOMATIQUE]) && $action_properties[Action::ACTION_AUTOMATIQUE] != self::ORIENTATION){
					$result[DocumentType::ACTION][$this->getActionID($typeDossierData,$num_etape,$action_id)][Action::ACTION_AUTOMATIQUE] =
						$this->getActionID($typeDossierData,$num_etape,$action_properties[Action::ACTION_AUTOMATIQUE]);
				}
			}
		}
	}

	private function setLastActionProperties(TypeDossierData $typeDossierData, array & $result){
		foreach($typeDossierData->etape as $num_etape => $etape){
			$action_list = $this->typeDossierEtapeDefinition->getAction($etape->type);

			foreach($action_list as $action_id => $action_properties) {
				if (empty($action_properties['rule'][Action::ACTION_RULE_LAST_ACTION])) {
					continue;
				}
				foreach($action_properties['rule'][Action::ACTION_RULE_LAST_ACTION] as $num_last_action => $last_action) {
					if ($last_action == self::ORIENTATION) {
						continue;
					}
					$result[DocumentType::ACTION][$this->getActionID($typeDossierData,$num_etape,$action_id)]['rule'][Action::ACTION_RULE_LAST_ACTION][$num_last_action] =
						$this->getActionID($typeDossierData,$num_etape,$last_action);
				}

			}
		}
	}


	private function setBaseAction(TypeDossierData $typeDossierData, array & $result){
		foreach($typeDossierData->etape as $num_etape => $etape){
			$action_list = $this->typeDossierEtapeDefinition->getAction($etape->type);

			foreach($action_list as $action_id => $action_properties) {
				$result[DocumentType::ACTION][$this->getActionID($typeDossierData, $num_etape, $action_id)] = $action_properties;
			}

		}
	}


	private function setActionAutomatique(TypeDossierData $typeDossierData, array & $result){
		foreach($typeDossierData->etape as $num_etape => $etape) {
			foreach ($this->typeDossierEtapeDefinition->getAction($etape->type) as $action_id => $action_properties) {
				if (isset($action_properties[Action::ACTION_AUTOMATIQUE]) && $action_properties[Action::ACTION_AUTOMATIQUE] == self::ORIENTATION) {
					$action_id = $this->getActionID($typeDossierData,$num_etape,$action_id);
					$result['action'][self::ORIENTATION]['rule'][Action::ACTION_RULE_LAST_ACTION][] = $action_id;
					if (! $etape->automatique){
						unset($result['action'][$action_id][Action::ACTION_AUTOMATIQUE]);
					}
				}
			}
		}
	}

	private function getActionID(TypeDossierData $typeDossierData,$num_etape,$action_name){
		$type_etape = $typeDossierData->etape[$num_etape]->type;
		$nb_type_total = 0;
		$true_num_etape = 1;
		foreach($typeDossierData->etape as  $n => $etape){
			if ($etape->type == $type_etape){
				$nb_type_total++;
			}
			if ($n==$num_etape){
				$true_num_etape = $nb_type_total;
			}
		}

		if ($nb_type_total == 1){
			return $action_name;
		}
		return $action_name."_".$true_num_etape;
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