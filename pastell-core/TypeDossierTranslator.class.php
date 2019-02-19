<?php

class TypeDossierTranslator {

	private $ymlLoader;
	private $typeDossierEtapeDefinition;

	public function __construct(
		YMLLoader $ymlLoader,
		TypeDossierEtapeDefinition $typeDossierEtapeDefinition
	) {
		$this->ymlLoader = $ymlLoader;
		$this->typeDossierEtapeDefinition = $typeDossierEtapeDefinition;
	}

	public function getDefinition(TypeDossierData $typeDossierData){

		$result = $this->ymlLoader->getArray(__DIR__."/../common-yaml/type-dossier-starter-kit.yml");

		$result['nom'] = $typeDossierData->nom;;
		$result['type'] = $typeDossierData->type;
		$result['description'] = $typeDossierData->description;

		$result['formulaire'] = [];

		$onglet_name = $typeDossierData->nom_onglet?:'onglet1';


		foreach($typeDossierData->formulaireElement as $element_id => $typeDossierFormulaireElement){
			$result['formulaire'][$onglet_name][$element_id] = [
				'name' => $typeDossierFormulaireElement->name?:$element_id,
				'type' => $this->getType($typeDossierFormulaireElement),
				'requis' => $typeDossierFormulaireElement->requis?"true":"false",
				'multiple' => $typeDossierFormulaireElement->type=='multi_file'?"true":"false",
				'commentaire' => $typeDossierFormulaireElement->commentaire
			];
			if ($typeDossierFormulaireElement->titre){
				$result['formulaire'][$onglet_name][$element_id]['title'] = "true";
			}
			if ($typeDossierFormulaireElement->champs_recherche_avancee || $typeDossierFormulaireElement->champs_affiches){
				$result['formulaire'][$onglet_name][$element_id]['index'] = "true";
			}
			if ($typeDossierFormulaireElement->champs_affiches){
				$result['champs-affiches'][] = $element_id;
			}
			if ($typeDossierFormulaireElement->champs_recherche_avancee){
				$result['champs-recherche-avancee'][] = $element_id;
			}
		}

		//Cheminement
		$has_cheminement_onglet = false;
		foreach($typeDossierData->etape as $etape) {
			if (! $etape->requis){
				$has_cheminement_onglet = true;
			}
			$cheminement[$etape->type] = [
				'libelle'=>TypeDossierDefinition::getTypeEtapeLibelle($etape->type),
				'requis' => $etape->requis
			];
		}
		if ($has_cheminement_onglet) {
			foreach ($cheminement as $etape_id => $etape_info) {
				$result['formulaire']['Cheminement']["envoi_$etape_id"] =
					[
						'name' => $etape_info['libelle'],
						'type' => 'checkbox',
						'onchange' => 'cheminement-change',
						'default' => $etape_info['requis']?"checked":"",
						'read-only' => $etape_info['requis']?true:false
					];
			}
			$result['action']['cheminement-change'] = [
				'no-workflow' => true,
				'rule'=> ['role_id_e'=>'no-role'],
				'action-class' => 'CheminementChangeFluxSpecifique'
			];

		}



		foreach($typeDossierData->etape as $etape){
			$result['connecteur'][] = $this->getConnecteurType($etape);
			$action_list = $this->typeDossierEtapeDefinition->getAction($etape->type);

			foreach($action_list as $action_id => $action) {
				$result['action'][$action_id] = $action;
			}
		}

		return $result;
	}

	private function getType(TypeDossierFormulaireElement $typeDossierFormulaireElement){
		if ($typeDossierFormulaireElement->type == 'multi_file'){
			return 'file';
		}
		return $typeDossierFormulaireElement->type;
	}

	private function getConnecteurType(TypeDossierEtape $etape){
		if ($etape->type=='depot'){
			return "GED";
		}
		return $etape->type;
	}

}