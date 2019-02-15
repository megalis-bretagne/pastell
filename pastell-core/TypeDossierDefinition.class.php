<?php

class TypeDossierDefinition {

	private $ymlLoader;
	private $workspace_path;

	public function __construct(
		YMLLoader $yml_loader,
		$workspacePath
	) {
		$this->ymlLoader = $yml_loader;
		$this->workspace_path = $workspacePath;
	}

	public static function getElementFormulaire(){
		return ['element_id','name','type','commentaire','requis','champs-affiches','champs-recherche-avancee'];
	}

	public function getEtapeFormulaire(){
		return ['num_etape','type','requis','document_a_signer','annexe','choix_type_parapheur'];
	}


	public static function getAllTypeElement(){
		return ['text'=>'Texte (une ligne)','file'=>'Fichier','multi_file'=>'Fichier(s) multiple(s)','textarea'=>'Zone de texte (multi-ligne)','password'=>'Mot de passe','checkbox'=>'Case à cocher','date'=>'Date'];
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
	 * @throws Exception
	 */
	public function create($id_t){
		$this->ymlLoader->saveArray($this->getDefinitionPath($id_t),[]);
	}

	/**
	 * @param $id_t
	 * @return array|bool
	 */
	public function getInfo($id_t){
		$info =  $this->ymlLoader->getArray($this->getDefinitionPath($id_t));

		foreach(array('nom','type','description') as $key) {
			if (!isset($info[$key])) {
				$info[$key] = '';
			}
		}

		return $info;
	}

	private function getDefinitionPath($id_t){
		return $this->workspace_path."/type_dossier_{$id_t}.yml";
	}

	/**
	 * @param $id_t
	 * @param $nom
	 * @throws Exception
	 */
	public function editLibelleInfo($id_t,$nom,$type,$description){
		$info = $this->getInfo($id_t);
		//TODO Faire un objet
		$info['nom'] = $nom;
		$info['type'] = $type;
		$info['description'] = $description;
		$this->save($id_t,$info);
	}

	/**
	 * @param $id_t
	 * @param $info
	 * @throws Exception
	 */
	private function save($id_t,array $info){
		$this->ymlLoader->saveArray($this->getDefinitionPath($id_t),$info);
	}


	public function getElementInfo($id_t,$element_id){
		$info = $this->getInfo($id_t);
		if (! isset($info['formulaire'][$element_id])){
			return array_fill_keys(
				self::getElementFormulaire(),
				''
			);
		}
		$result =  $info['formulaire'][$element_id];
		$result['element_id'] = $element_id;
		return $result;
	}

	/**
	 * @param $id_t
	 * @param Recuperateur $recuperateur
	 * @throws Exception
	 */
	public function editionElement($id_t,Recuperateur $recuperateur){
		$info = $this->getInfo($id_t);

		$element_id = $recuperateur->get('element_id');
		if (! $element_id){
			throw new Exception("L'identifiant de l'élément est obligatoire");
		}
		$orig_element_id = $recuperateur->get('orig_element_id');
		if ($orig_element_id && $orig_element_id != $element_id){
			$info['formulaire'][$element_id] = $info['formulaire'][$orig_element_id];
			unset($info['formulaire'][$orig_element_id]);
		}
		if (! isset($info['formulaire'][$element_id])){
			$info['formulaire'][$element_id] = [];
		}
		$element_formulaire_list = self::getElementFormulaire();
		$element_formulaire_list = array_diff($element_formulaire_list,['element_id']);

		foreach ($element_formulaire_list as $element_formulaire){
			$info['formulaire'][$element_id][$element_formulaire] = $recuperateur->get($element_formulaire);
		}

		$this->save($id_t,$info);
	}

	/**
	 * @param $id_t
	 * @param $element_id
	 * @throws Exception
	 */
	public function deleteElement($id_t,$element_id){
		$info = $this->getInfo($id_t);
		unset($info['formulaire'][$element_id]);
		$this->save($id_t,$info);
	}

    public function sortElement($id_t,$tr){
        $info = $this->getInfo($id_t);
        $new_form = [];
        foreach($tr as $element_id){
            $new_form[$element_id] = $info['formulaire'][$element_id];
        }
        if (count($new_form) != count($info['formulaire'])){
            throw new Exception("Impossible de retrier le tableau");
        }
        $info['formulaire'] = $new_form;
        $this->save($id_t,$info);
    }

	public function getEtapeInfo($id_t,$num_etape ){

		if ( $num_etape === 'new'){
			return array_fill_keys(
				self::getEtapeFormulaire(),
				''
			);
		}

		$info = $this->getInfo($id_t);
		$result =  $info['cheminement'][$num_etape];
		$result['num_etape'] = $num_etape;
		return $result;
	}

	/**
	 * @param $id_t
	 * @param Recuperateur $recuperateur
	 * @throws Exception
	 */
	public function editionEtape($id_t, Recuperateur $recuperateur){
		$info = $this->getInfo($id_t);

		$num_etape = $recuperateur->getInt('num_etape');

		if (! $num_etape){
			$info['cheminement'][] = [];
			$num_etape = count($info['cheminement']) - 1;
		}

		$element_formulaire_list = self::getEtapeFormulaire();
		$element_formulaire_list = array_diff($element_formulaire_list,['num_etape']);

		foreach ($element_formulaire_list as $element_formulaire){
			$info['cheminement'][$num_etape][$element_formulaire] = $recuperateur->get($element_formulaire);
		}
		$this->save($id_t,$info);
	}

	/**
	 * @param $id_t
	 * @param $num_etape
	 * @throws Exception
	 */
	public function deleteEtape($id_t,$num_etape){
		$info = $this->getInfo($id_t);
		array_splice($info['cheminement'],$num_etape,1);
		$this->save($id_t,$info);
	}

	public function getFieldWithType($id_t,$type){
		$result = [];
		$info = $this->getInfo($id_t);
		foreach($info['formulaire'] as $element_id => $element_info){
			if ($element_info['type'] == $type){
				$result[$element_id] = $element_info;
			}
		}
		return $result;
	}


    public function sortEtape($id_t,$tr){
	    print_r($tr);
        $info = $this->getInfo($id_t);
        $new_cheminement = [];
        foreach($tr as $num_etape){
            $new_cheminement[] = $info['cheminement'][$num_etape];
        }
        if (count($new_cheminement) != count($info['cheminement'])){
            throw new Exception("Impossible de retrier le tableau");
        }
        $info['cheminement'] = $new_cheminement;
        $this->save($id_t,$info);
    }
}