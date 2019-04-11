<?php

class TypeDossierService {

	const TYPE_DOSSIER_ID_MAX_LENGTH=32;
	const TYPE_DOSSIER_ID_REGEXP = "^[0-9a-z-]+$";

	private $ymlLoader;
	private $workspace_path;
	private $typeDossierPersonnaliseDirectoryManager;
	private $typeDossierEtapeDefinition;
	private $typeDossierSQL;
	private $documentSQL;

	public function __construct(
		YMLLoader $yml_loader,
		$workspacePath,
		TypeDossierPersonnaliseDirectoryManager $typeDossierPersonnaliseDirectoryManager,
		TypeDossierEtapeManager $typeDossierEtapeDefinition,
		TypeDossierSQL $typeDossierSQL,
		Document $documentSQL
	) {
		$this->ymlLoader = $yml_loader;
		$this->workspace_path = $workspacePath;
		$this->typeDossierPersonnaliseDirectoryManager = $typeDossierPersonnaliseDirectoryManager;
		$this->typeDossierEtapeDefinition = $typeDossierEtapeDefinition;
		$this->typeDossierSQL = $typeDossierSQL;
		$this->documentSQL = $documentSQL;
	}

	public function create(string $id_type_dossier) : int{
		$typeDossierProperties = new TypeDossierProperties();
		$typeDossierProperties->id_type_dossier = $id_type_dossier;
		return $this->typeDossierSQL->edit(0,$typeDossierProperties);
	}

	public function getTypeDossierPropertiesFromFilepath(string $filepath) : TypeDossierProperties {
		$file_content = file_get_contents($filepath);
		$json = json_decode($file_content,true);
		return $this->getTypeDossierFromArray($json);
	}

	/**
	 * @param $id_t
	 * @param TypeDossierProperties $typeDossierData
	 * @return string
	 * @throws Exception
	 */
	public function save($id_t, TypeDossierProperties $typeDossierData){
		$id_t = $this->typeDossierSQL->edit($id_t,$typeDossierData);
		$this->typeDossierPersonnaliseDirectoryManager->save($id_t,$typeDossierData);
		return $id_t;
	}

	/**
	 * @param $id_t
	 */
	public function delete($id_t){
		$this->typeDossierSQL->delete($id_t);
		$this->typeDossierPersonnaliseDirectoryManager->delete($id_t);
	}

	/**
	 * @param $id_t
	 * @return mixed
	 */
	public function getRawData($id_t){
		return $this->typeDossierSQL->getTypeDossierArray($id_t);
	}

	/**
	 * @param $id_t
	 * @return TypeDossierProperties
	 */
	public function getTypeDossierProperties($id_t){
		$info = $this->getRawData($id_t)?:[];
		return $this->getTypeDossierFromArray($info);
	}

	public function getTypeDossierFromArray(array $info){
		$result = new TypeDossierProperties();

		foreach(array('id_type_dossier','nom','type','description','nom_onglet') as $key) {
			if (isset($info[$key])) {
				$result->$key = $info[$key];
			}
		}
		if (empty($info['formulaireElement'])){
			$info['formulaireElement'] = [];
		}
		if (empty($info['etape'])){
			$info['etape'] = [];
		}

		$result->formulaireElement = [];
		$typeDossierFormulaireElementManager = new TypeDossierFormulaireElementManager();

		foreach($info['formulaireElement'] as $formulaire_element){
			$newFormElement = $typeDossierFormulaireElementManager->getElementFromArray($formulaire_element);
			$result->formulaireElement[] = $newFormElement;
		}

		$result->etape = [];

		foreach($info['etape'] as $etape){
			$fomulaire_configuration = $this->typeDossierEtapeDefinition->getFormulaireConfigurationEtape($etape['type']);
			$newFormEtape = $this->typeDossierEtapeDefinition->getEtapeFromArray($etape,$fomulaire_configuration);
			$result->etape[$newFormEtape->num_etape?:0] = $newFormEtape;
		}
		$sum_type_etape = [];
		foreach($result->etape as $etape){
			if (! isset($sum_type_etape[$etape->type])){
				$sum_type_etape[$etape->type] = 0;
			} else {
				$sum_type_etape[$etape->type]++;
			}
			$etape->num_etape_same_type = $sum_type_etape[$etape->type];
		}
		foreach($result->etape as $etape){
			if ($sum_type_etape[$etape->type] > 0){
				$etape->etape_with_same_type_exists = true;
			}
		}

		return $result;
	}

	/**
	 * @param $id_t
	 * @param $nom
	 * @param $type
	 * @param $description
	 * @param $nom_onglet
	 * @throws Exception
	 */
	public function editLibelleInfo($id_t,$nom,$type,$description,$nom_onglet){
		$typeDossierData = $this->getTypeDossierProperties($id_t);
		$typeDossierData->nom = $nom;
		$typeDossierData->type = $type;
		$typeDossierData->description = $description;
		$typeDossierData->nom_onglet = $nom_onglet;
		$this->save($id_t,$typeDossierData);
	}

	public function getFormulaireElement($id_t, $element_id){
		$typeDossierData = $this->getTypeDossierProperties($id_t);
		return $this->getFormulaireElementFromProperties($typeDossierData,$element_id);
	}

	public function getFormulaireElementFromProperties(TypeDossierProperties $typeDossierProperties,$element_id){
		foreach($typeDossierProperties->formulaireElement as $formulaireElementProperties){
			if ($formulaireElementProperties->element_id == $element_id){
				return $formulaireElementProperties;
			}
		}
		return new TypeDossierFormulaireElementProperties();
	}

	public function hasFormulaireElement($typeDossierProperties,$element_id){
		foreach($typeDossierProperties->formulaireElement as $formulaireElementProperties){
			if ($formulaireElementProperties->element_id == $element_id){
				return true;
			}
		}
		return false;
	}

	/**
	 * @param $typeDossierProperties
	 * @param $element_id
	 * @return int|string
	 * @throws TypeDossierException
	 */
	public function getFormulaireElementIndex($typeDossierProperties,$element_id){
		foreach($typeDossierProperties->formulaireElement as $i => $formulaireElementProperties){
			if ($formulaireElementProperties->element_id == $element_id){
				return $i;
			}
		}
		throw new TypeDossierException("L'élement $element_id n'existe pas");
	}

	/**
	 * @param $id_t
	 * @param Recuperateur $recuperateur
	 * @throws Exception
	 */
	public function editionElement($id_t,Recuperateur $recuperateur){
		$typeDossierData = $this->getTypeDossierProperties($id_t);

		$element_id = $recuperateur->get('element_id');
		if (! $element_id){
			throw new TypeDossierException("L'identifiant de l'élément est obligatoire");
		}
		$orig_element_id = $recuperateur->get('orig_element_id');
		if ($orig_element_id && $orig_element_id != $element_id){
			$element =  $this->getFormulaireElementFromProperties($typeDossierData,$orig_element_id);
			$element->element_id = $element_id;
		}

		if ($recuperateur->get('titre')){
			foreach($typeDossierData->formulaireElement as $formulaireElement){
				$formulaireElement->titre = false;
			}
		}

		$formulaireElement = $this->getFormulaireElementFromProperties($typeDossierData,$element_id);
		if (! $orig_element_id){
			$typeDossierData->formulaireElement[] = $formulaireElement;
		}
        $typeDossierFormulaireElementManager = new TypeDossierFormulaireElementManager();


		$typeDossierFormulaireElementManager->edition(
			$formulaireElement,
            $recuperateur
        );

		$this->save($id_t,$typeDossierData);
	}

	/**
	 * @param $id_t
	 * @param $element_id
	 * @throws Exception
	 */
	public function deleteElement($id_t,$element_id){
		$typeDossierData = $this->getTypeDossierProperties($id_t);

		$element_index = $this->getFormulaireElementIndex($typeDossierData,$element_id);

		unset($typeDossierData->formulaireElement[$element_index]);
		$this->save($id_t,$typeDossierData);
	}

	/**
	 * @param $id_t
	 * @param $tr
	 * @throws Exception
	 */
    public function sortElement($id_t,array $tr){
        $typeDossierData = $this->getTypeDossierProperties($id_t);
        $new_form = [];
        foreach($tr as $element_id){
            $new_form[] = $this->getFormulaireElementFromProperties($typeDossierData,$element_id);
        }

        if (count($new_form) != count($typeDossierData->formulaireElement)){
            throw new TypeDossierException("Impossible de retrier le tableau");
        }
		$typeDossierData->formulaireElement = $new_form;
        $this->save($id_t,$typeDossierData);
    }

    public function getFieldWithType($id_t,$type){
        $result = [];
        $info = $this->getTypeDossierProperties($id_t);
        foreach($info->formulaireElement as $element_id => $element_info){
            if ($element_info->type == $type){
                $result[$element_info->element_id] = $element_info;
            }
        }
        return $result;
    }

    public function getEtapeInfo($id_t,$num_etape ) : TypeDossierEtapeProperties{
		$typeDossierData = $this->getTypeDossierProperties($id_t);
		if (! isset($typeDossierData->etape[$num_etape])){
			$result =  new TypeDossierEtapeProperties();
			$result->num_etape = 'new';
			return $result;
		}
		return $typeDossierData->etape[$num_etape];
	}


	/**
	 * @param $id_t
	 * @param Recuperateur $recuperateur
	 * @throws Exception
	 */
	public function newEtape($id_t,Recuperateur $recuperateur){
		$typeDossierData = $this->getTypeDossierProperties($id_t);
		$typeDossierEtape = $this->getTypeDossierEtapeFromRecuperateur(
		    $recuperateur,
            $recuperateur->get('type')
        );
		$typeDossierData->etape[] = $typeDossierEtape;

		$num_etape = count($typeDossierData->etape) - 1;
		$typeDossierEtape->num_etape = $num_etape?:0;
		$this->save($id_t,$typeDossierData);
	}

	/**
	 * @param $id_t
	 * @param Recuperateur $recuperateur
	 * @throws Exception
	 */
	public function editionEtape($id_t, Recuperateur $recuperateur){
		$num_etape = $recuperateur->get('num_etape')?:0;

		$typeDossierData = $this->getTypeDossierProperties($id_t);
		$type = $typeDossierData->etape[$num_etape]->type;
		$typeDossierEtape = $this->getTypeDossierEtapeFromRecuperateur($recuperateur,$type);
		$typeDossierData->etape[$num_etape] = $typeDossierEtape;
		$typeDossierEtape->type = $type;
		$typeDossierEtape->num_etape = $num_etape?:0;
		$this->save($id_t,$typeDossierData);
	}

	private function getTypeDossierEtapeFromRecuperateur(Recuperateur $recuperateur,$type) : TypeDossierEtapeProperties {
		$typeDossierEtape = new TypeDossierEtapeProperties();

		foreach (TypeDossierEtapeManager::getPropertiesId() as $element_formulaire){
			$typeDossierEtape->$element_formulaire = $recuperateur->get($element_formulaire);
		}

		$configuration_etape = $this->typeDossierEtapeDefinition->getFormulaireConfigurationEtape($type);
		foreach($configuration_etape as $element_id => $element_info){
			$typeDossierEtape->specific_type_info[$element_id] = $recuperateur->get($element_id);
		}
		return $typeDossierEtape;
	}

	/**
	 * @param $id_t
	 * @param $num_etape
	 * @throws Exception
	 */
	public function deleteEtape($id_t,$num_etape){
		$typeDossierData = $this->getTypeDossierProperties($id_t);
		array_splice($typeDossierData->etape,$num_etape,1);
		foreach($typeDossierData->etape as $i => $etape){
			$typeDossierData->etape[$i]->num_etape = $i;
		}

		$this->save($id_t,$typeDossierData);
	}

	/**
	 * @param $id_t
	 * @param $tr
	 * @throws Exception
	 */
    public function sortEtape($id_t,$tr){
        $typeDossierData = $this->getTypeDossierProperties($id_t);
        $new_cheminement = [];
        foreach($tr as $num_etape){
            $new_cheminement[] = $typeDossierData->etape[$num_etape];
        }
        if (count($new_cheminement) != count($typeDossierData->etape)){
            throw new TypeDossierException("Impossible de retrier le tableau");
        }
        $typeDossierData->etape = $new_cheminement;
		foreach($typeDossierData->etape as $i => $etape){
			$typeDossierData->etape[$i]->num_etape = $i;
		}
        $this->save($id_t,$typeDossierData);
    }

    private function getEtapeList($typeDossier,$cheminement_list){
		$etapeList = [];
		foreach($typeDossier->etape as $num_etape => $etape){
			if (! isset($cheminement_list[$num_etape]) || $cheminement_list[$num_etape]){
				$etapeList[] = $etape;
			}
		}
		return $etapeList;
	}

	/**
	 * @param int $id_t
	 * @param string $action_source
	 * @return string
	 * @throws TypeDossierException
	 */
    public function getNextAction(int $id_t,string $action_source,array $cheminement_list = []) : string{
		$typeDossier = $this->getTypeDossierProperties($id_t);
		$etapeList = $this->getEtapeList($typeDossier,$cheminement_list);

		if (in_array($action_source,['creation','modification','importation'])){
			foreach($this->typeDossierEtapeDefinition->getActionForEtape($etapeList[0]) as $action_name => $action_properties){
				return $action_name;
			}
			throw new TypeDossierException("Impossible de trouver la première action a effectué sur le document");
		}

		foreach($etapeList as $num_etape => $etape){
			$action = $this->typeDossierEtapeDefinition->getActionForEtape($etape);
			foreach($action as $action_name => $action_info){
				if ($action_name == $action_source){
					if (empty( $etapeList[$num_etape +1])){
						return "termine";
					}
					$next_etape = $etapeList[$num_etape + 1];
					break 2;
				}
			}
		}

		$action_list = $this->typeDossierEtapeDefinition->getActionForEtape($next_etape);
		if ( ! $action_list){
			throw new TypeDossierException("Aucune action n'a été trouvée");
		}
		return array_keys($action_list)[0];
	}

    /**
     * @param $id_t
     * @throws Exception
     */
	public function reGenerate($id_t){
        $typeDossierData = $this->getTypeDossierProperties($id_t);
        $this->save($id_t,$typeDossierData);
    }

}