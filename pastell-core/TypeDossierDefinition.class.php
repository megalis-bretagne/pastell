<?php

class TypeDossierDefinition {

	private $ymlLoader;
	private $workspace_path;
	private $typeDossierPersonnaliseDirectoryManager;
	private $typeDossierEtapeDefinition;

	public function __construct(
		YMLLoader $yml_loader,
		$workspacePath,
		TypeDossierPersonnaliseDirectoryManager $typeDossierPersonnaliseDirectoryManager,
		TypeDossierEtapeDefinition $typeDossierEtapeDefinition
	) {
		$this->ymlLoader = $yml_loader;
		$this->workspace_path = $workspacePath;
		$this->typeDossierPersonnaliseDirectoryManager = $typeDossierPersonnaliseDirectoryManager;
		$this->typeDossierEtapeDefinition = $typeDossierEtapeDefinition;

	}

	public static function getAllTypeElement(){
		return [
			'text'=>'Texte (une ligne)',
			'file'=>'Fichier',
			'multi_file'=>'Fichier(s) multiple(s)',
			'textarea'=>'Zone de texte (multi-ligne)',
			'password'=>'Mot de passe',
			'checkbox'=>'Case à cocher',
			'date'=>'Date'
		];
	}

	public static function getTypeElementLibelle($id){
		//TODO vérifier l'existence
		return self::getAllTypeElement()[$id];
	}

	public static function getAllTypeEtape(){
		return [
			'signature'=>'Visa/Signature',
			'TdT' => 'Tiers de télétransmission (DGCL, DGFip)',
			'depot' => 'Dépôt (GED, FTP, ...)',
			'mailsec' => 'Mail sécurisé',
			'sae' => "Système d'archivage électronique"
		];
	}

	public static function getTypeEtapeLibelle($id){
		//TODO vérifier l'existence
		return self::getAllTypeEtape()[$id];
	}

	/**
	 * @param $id_t
	 * @param $typeDossierData
	 * @throws Exception
	 */
	private function save($id_t,TypeDossierData $typeDossierData){
		file_put_contents($this->getDefinitionPath($id_t),json_encode($typeDossierData));
		$this->typeDossierPersonnaliseDirectoryManager->save($id_t,$typeDossierData);
	}

	/**
	 * @param $id_t
	 * @throws Exception
	 */
	public function create($id_t){
		$this->save($id_t,new TypeDossierData());
	}

	public function delete($id_t){
		unlink($this->getDefinitionPath($id_t));
		$this->typeDossierPersonnaliseDirectoryManager->delete($id_t);
	}

	/**
	 * @param $id_t
	 * @return TypeDossierData
	 */
	public function getTypeDossierData($id_t){
		$definition_file = $this->getDefinitionPath($id_t);
		if (file_exists($definition_file)){
			//TODO a refactorer
			$info = json_decode(file_get_contents($definition_file),true);
		} else {
			$info = [];
		}

		$result = new TypeDossierData();

		foreach(array('nom','type','description','nom_onglet') as $key) {
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

		foreach($info['formulaireElement'] as $formulaireElement){
			$newFormElement = new TypeDossierFormulaireElement();
			foreach(get_class_vars(TypeDossierFormulaireElement::class) as $key => $value){
				if (isset($formulaireElement[$key])) {
					$newFormElement->$key = $formulaireElement[$key];
				} else {
					$newFormElement->$key = false;
				}
			}
			$result->formulaireElement[$newFormElement->element_id] = $newFormElement;
		}

		$result->etape = [];

		foreach($info['etape'] as $etape){
			$newFormEtape = new TypeDossierEtape();
			foreach(get_class_vars(TypeDossierEtape::class) as $key => $value){
				if (isset($etape[$key])) {
					$newFormEtape->$key = $etape[$key];
				}
			}
            if ($newFormEtape->specific_type_info == ""){
                $newFormEtape->specific_type_info = [];
            }

			$fomulaire_configuration = $this->typeDossierEtapeDefinition->getFormulaireConfigurationEtape($newFormEtape->type);
			foreach($fomulaire_configuration as $element_id => $element_info){
				if (! isset($newFormEtape->specific_type_info[$element_id])){
					$newFormEtape->specific_type_info[$element_id] = "";
				}
			}

			$result->etape[$newFormEtape->num_etape?:0] = $newFormEtape;
		}

		return $result;
	}

	private function getDefinitionPath($id_t){
		return $this->workspace_path."/type_dossier_{$id_t}.json";
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
		$typeDossierData = $this->getTypeDossierData($id_t);
		$typeDossierData->nom = $nom;
		$typeDossierData->type = $type;
		$typeDossierData->description = $description;
		$typeDossierData->nom_onglet = $nom_onglet;
		$this->save($id_t,$typeDossierData);
	}

	public function getFormulaireElement($id_t, $element_id){
		$typeDossierData = $this->getTypeDossierData($id_t);
		if (! isset($typeDossierData->formulaireElement[$element_id])){
			return new TypeDossierFormulaireElement();
		}
		return $typeDossierData->formulaireElement[$element_id];
	}

	/**
	 * @param $id_t
	 * @param Recuperateur $recuperateur
	 * @throws Exception
	 */
	public function editionElement($id_t,Recuperateur $recuperateur){
		$typeDossierData = $this->getTypeDossierData($id_t);

		$element_id = $recuperateur->get('element_id');
		if (! $element_id){
			throw new Exception("L'identifiant de l'élément est obligatoire");
		}
		$orig_element_id = $recuperateur->get('orig_element_id');
		if ($orig_element_id && $orig_element_id != $element_id){
			$typeDossierData->formulaireElement[$element_id] = $typeDossierData->formulaireElement[$orig_element_id];
			unset($typeDossierData->formulaireElement[$orig_element_id]);
		}
		if (! isset($typeDossierData->formulaireElement[$element_id])){
			$typeDossierData->formulaireElement[$element_id] = new TypeDossierFormulaireElement();
		}
		$element_formulaire_list = get_class_vars(TypeDossierFormulaireElement::class);

		if ($recuperateur->get('titre')){
			foreach($typeDossierData->formulaireElement as $formulaireElement){
				$formulaireElement->titre = false;
			}
		}

		foreach ($element_formulaire_list as $element_formulaire => $element_value){
			$typeDossierData->formulaireElement[$element_id]->$element_formulaire = $recuperateur->get($element_formulaire);
		}

		$this->save($id_t,$typeDossierData);
	}

	/**
	 * @param $id_t
	 * @param $element_id
	 * @throws Exception
	 */
	public function deleteElement($id_t,$element_id){
		$typeDossierData = $this->getTypeDossierData($id_t);
		unset($typeDossierData->formulaireElement[$element_id]);
		$this->save($id_t,$typeDossierData);
	}

	/**
	 * @param $id_t
	 * @param $tr
	 * @throws Exception
	 */
    public function sortElement($id_t,$tr){
        $typeDossierData = $this->getTypeDossierData($id_t);
        $new_form = [];
        foreach($tr as $element_id){
            $new_form[$element_id] = $typeDossierData->formulaireElement[$element_id];
        }

        if (count($new_form) != count($typeDossierData->formulaireElement)){
            throw new Exception("Impossible de retrier le tableau");
        }
		$typeDossierData->formulaireElement = $new_form;
        $this->save($id_t,$typeDossierData);
    }

	public function getEtapeInfo($id_t,$num_etape ){
		$typeDossierData = $this->getTypeDossierData($id_t);
		if (! isset($typeDossierData->etape[$num_etape])){
			$result =  new TypeDossierEtape();
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
		$typeDossierData = $this->getTypeDossierData($id_t);
		$typeDossierEtape = $this->getTypeDossierEtapeFromRecuperateur($recuperateur,$recuperateur->get('type'));
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

		$typeDossierData = $this->getTypeDossierData($id_t);
		$type = $typeDossierData->etape[$num_etape]->type;
		$typeDossierEtape = $this->getTypeDossierEtapeFromRecuperateur($recuperateur,$type);
		$typeDossierData->etape[$num_etape] = $typeDossierEtape;
		$typeDossierEtape->type = $type;
		$typeDossierEtape->num_etape = $num_etape?:0;

		$this->save($id_t,$typeDossierData);
	}

	private function getTypeDossierEtapeFromRecuperateur(Recuperateur $recuperateur,$type) : TypeDossierEtape {
		$typeDossierEtape = new TypeDossierEtape();
		$element_formulaire_list = get_class_vars(TypeDossierEtape::class);

		foreach ($element_formulaire_list as $element_formulaire => $value){
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
		$typeDossierData = $this->getTypeDossierData($id_t);
		array_splice($typeDossierData->etape,$num_etape,1);
		foreach($typeDossierData->etape as $i => $etape){
			$typeDossierData->etape[$i]->num_etape = $i;
		}

		$this->save($id_t,$typeDossierData);
	}

	public function getFieldWithType($id_t,$type){
		$result = [];
		$info = $this->getTypeDossierData($id_t);
		foreach($info->formulaireElement as $element_id => $element_info){
			if ($element_info->type == $type){
				$result[$element_id] = $element_info;
			}
		}
		return $result;
	}


	/**
	 * @param $id_t
	 * @param $tr
	 * @throws Exception
	 */
    public function sortEtape($id_t,$tr){
	    print_r($tr);
        $typeDossierData = $this->getTypeDossierData($id_t);
        $new_cheminement = [];
        foreach($tr as $num_etape){
            $new_cheminement[] = $typeDossierData->etape[$num_etape];
        }
        if (count($new_cheminement) != count($typeDossierData->etape)){
            throw new Exception("Impossible de retrier le tableau");
        }
        $typeDossierData->etape = $new_cheminement;
		foreach($typeDossierData->etape as $i => $etape){
			$typeDossierData->etape[$i]->num_etape = $i;
		}
        $this->save($id_t,$typeDossierData);
    }

}