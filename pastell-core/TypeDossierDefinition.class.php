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

}