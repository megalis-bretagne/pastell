<?php 
class Extensions {
	
	const MODULE_FOLDER_NAME = "module";
	const CONNECTEUR_FOLDER_NAME = "connecteur";
	const CONNECTEUR_TYPE_FOLDER_NAME = "connecteur-type";
	const TYPE_DOSSIER_FOLDER_NAME = "type-dossier";

	const PASTELL_ALL_MODULE_CACHE_KEY="pastell_all_module";
	const PASTELL_ALL_CONNECTEUR_CACHE_KEY="pastell_all_connecteur";
	const PASTELL_CONNECTEUR_TYPE_PATH_CACHE_KEY = "pastell_connecteur_type";
	const PASTELL_ALL_TYPE_DOSSIER_CACHE_KEY = "pastell_all_type_dossier";

	private $extensionSQL;
	private $manifestFactory;
	private $pastell_path;

	private $memoryCache;
	private $cache_ttl_in_seconds;
	private $workspace_path;


	/**
	 *
	 * @param ExtensionSQL $extensionSQL
	 * @param ManifestFactory $manifestFactory
	 * @param String $pastell_path racine des fichiers Pastell
	 * @param MemoryCache
	 * @param int cache_ttl_in_seconds
	 */
	public function __construct(
		ExtensionSQL $extensionSQL,
		ManifestFactory $manifestFactory,
		$pastell_path,
		MemoryCache $memoryCache,
		$cache_ttl_in_seconds,
		$workspacePath
	){
		$this->extensionSQL = $extensionSQL;
		$this->manifestFactory = $manifestFactory;
		$this->pastell_path = $pastell_path;
		$this->memoryCache = $memoryCache;
		$this->cache_ttl_in_seconds = $cache_ttl_in_seconds;
		$this->workspace_path = $workspacePath;
	}
	
	public function getAll(){
		$extensions_list = array();
		foreach($this->extensionSQL->getAll() as $extension){
			$extensions_list[$extension['id_e']] = $this->getInfo($extension['id_e']); 
		}
		uasort($extensions_list,array($this,"compareExtension"));
		return $extensions_list;
	}
	
	private function compareExtension($a,$b){
		return strcmp($a['nom'], $b['nom']);
	}

	public function getById($id){
		foreach($this->getAll() as $id_e => $info){
			if ($info['id'] == $id){
				return $info;
			}
		}
		return false;
	}


	public function getAllConnecteur(){
		$result = $this->memoryCache->fetch(self::PASTELL_ALL_CONNECTEUR_CACHE_KEY);
		if ($result){
			return $result;
		}
		$result = array();
		foreach($this->getAllExtensionsPath() as $search){
			foreach($this->getAllConnecteurByPath($search) as $id_connecteur){
				$result[$id_connecteur] = $search."/".self::CONNECTEUR_FOLDER_NAME."/$id_connecteur";
			}
		}
		$this->memoryCache->store(
			self::PASTELL_ALL_CONNECTEUR_CACHE_KEY,
			$result,
			$this->cache_ttl_in_seconds
		);
		return $result;
	}
	
	public function getConnecteurPath($id_connecteur){
		$result = $this->getAllConnecteur();
		if (empty($result[$id_connecteur])){
			return false;
		}
		return $result[$id_connecteur];
	}



	public function getAllConnecteurType(){
		$result = array();
		foreach($this->getAllExtensionsPath() as $search){
			foreach($this->getAllConnecteurTypeByPath($search) as $id_connecteur){
				$result[$id_connecteur] = $search."/".self::CONNECTEUR_TYPE_FOLDER_NAME."/$id_connecteur";
			}
		}
		return $result;
	}

	public function getTypeDossierPath($type_etape){
		$result = $this->getAllTypeDossier();
		if (empty($result[$type_etape])){
			return false;
		}
		return $result[$type_etape];
	}


	public function getAllTypeDossier(){
		$result = $this->memoryCache->fetch(self::PASTELL_ALL_TYPE_DOSSIER_CACHE_KEY);
		if ($result){
			return $result;
		}
		$result = array();
		foreach($this->getAllExtensionsPath() as $search){
			foreach($this->getAllTypeDossierByPath($search) as $type_etape){
				$result[$type_etape] = $search."/".self::TYPE_DOSSIER_FOLDER_NAME."/$type_etape";
			}
		}
		$this->memoryCache->store(
			self::PASTELL_ALL_TYPE_DOSSIER_CACHE_KEY,
			$result,
			$this->cache_ttl_in_seconds
		);
		return $result;
	}


	
	private function getAllExtensionsPath(){
		$to_search = array($this->pastell_path);
		$to_search[] = $this->workspace_path."/".TypeDossierPersonnaliseDirectoryManager::SUB_DIRECTORY;
		foreach($this->extensionSQL->getAll() as $extension){
			$to_search[] = $extension['path'];
		}
		return $to_search;
	}
	
	public function getAllModule(){
		$result = $this->memoryCache->fetch(self::PASTELL_ALL_MODULE_CACHE_KEY);
		if ($result){
			return $result;
		}
		$result = array();
		foreach($this->getAllExtensionsPath() as $search){
			foreach($this->getAllModuleByPath($search) as $id_module){
				$result[$id_module] = $search."/".self::MODULE_FOLDER_NAME."/$id_module";
			}
		}

		$this->memoryCache->store(
			self::PASTELL_ALL_MODULE_CACHE_KEY,
			$result,
			$this->cache_ttl_in_seconds
		);
		return $result;
	}
	
	public function getModulePath($id_module_to_found){
		$result = $this->getAllModule();
		if (empty($result[$id_module_to_found])){
			return false;
		}
		return $result[$id_module_to_found];
	}

	public function getInfo($id_e, $path = null){

		if ($path) {
			$info = $this->getInfoFromPath($path);
		}
		else {
			$info = $this->extensionSQL->getInfo($id_e);
			$info = $this->getInfoFromPath($info['path']);
		}		
		$info['error'] = false;
		$info['warning'] = false;
		$info['pastell-version-ok'] = true;
		
		$info['id_e'] = $id_e;
		if (! file_exists($info['path'])){
			$info['error'] = "Extension non trouvée";
			$info['error-detail'] = "L'emplacement {$info['path']} n'a pas été trouvé sur le système de fichier";
			return $info;
		} 
		if (! $info['manifest']['nom']){
			$info['warning'] = "manifest.yml absent";
			$info['warning-detail'] = "Le fichier manifest.yml n'a pas été trouvé dans {$info['path']}";
			return $info;	
		} 
		
		$pastellManifest = $this->manifestFactory->getPastellManifest();
				
		if (! $pastellManifest->isVersionOK($info['manifest']['pastell-version'])) {
			$info['warning'] = "Version de pastell incorrecte";
			$info['warning-detail'] = "Ce module attend une version de Pastell ({$info['manifest']['pastell-version']}) non prise en charge par ce Pastell";
			$info['pastell-version-ok'] = false; 
			return $info;
		} 
		$extension_absente = array();
		$extension_bad_version= array();
		foreach($info['manifest']['extension_needed'] as $extension_needed => $extension_needed_info){
			$info['manifest']['extension_needed'][$extension_needed] = $this->checkExtensionNeeded($extension_needed, $extension_needed_info);
			if (! $info['manifest']['extension_needed'][$extension_needed]['extension_presente']){
				$extension_absente[] = $extension_needed;
			} else if (! $info['manifest']['extension_needed'][$extension_needed]['extension_version_ok']){
				$extension_bad_version[] = $extension_needed;
			}
		}
		
		if ($extension_absente) {
			$info['warning'] = "Extensions(s) manquante(s)";
			$info['warning-detail'] = "Cette extension dépend d'autres extensions qui ne sont pas installés sur cette instance de Pastell : " . implode(', ',$extension_absente);
			return $info;
		} 
		if ($extension_bad_version){
			$info['warning'] = "Mauvais numéro de version d'une dépendance";
			$info['warning-detail'] = "Ce extension dépend d'autres extensions qui ne sont pas dans une version attendue : " . implode(', ',$extension_bad_version);
			return $info;
		}
	
		return $info;
	}
	
	private function checkExtensionNeeded($extension_needed,$extension_needed_info){
		$extension_needed_info['extension_presente'] = false;
		$extension_needed_info['extension_version_ok'] = false;
		$info = $this->getInfoFromId($extension_needed);
		if (! $info){
			return $extension_needed_info;
		}
		
		$extension_needed_info['extension_presente'] = true;
		
		if (empty($extension_needed_info['version'])){
			return $extension_needed_info;
		}
		if (empty($info['manifest']['extensions_versions_accepted'])){
			return $extension_needed_info;
		}
		
		foreach($info['manifest']['extensions_versions_accepted'] as $version_accepted){
			if ($version_accepted == $extension_needed_info['version']){
				$extension_needed_info['extension_version_ok'] = true;
				return $extension_needed_info;
			}
		}
		
		return $extension_needed_info;
	}
	
	private function getInfoFromId($extension_id){
		foreach($this->extensionSQL->getAll() as $extension){
			$info = $this->getInfoFromPath($extension['path']);
			if ($info['id'] == $extension_id){
				return $info;	
			}
		}
		return false;
	}
	
	private function getInfoFromPath($path){
		$result['path'] = $path; 
		$result['flux'] = $this->getAllModuleByPath($path);
		$result['connecteur'] = $this->getAllConnecteurByPath($path);
		$result['connecteur-type'] = $this->getAllConnecteurTypeByPath($path);
		$manifest = $this->getManifest($path);
		$result['manifest'] = $manifest;
		$result['id'] = $manifest['id']?:basename($path);
		$result['nom'] = $manifest['nom']?:$result['id'];
		return $result;
	}
	
	private function getManifest($path){
		try {
			$manifest = $this->manifestFactory->getManifest($path);
		} catch (Exception $e){
			return false;
		}
		return $manifest->getInfo();
	}
	
	private function getAllModuleByPath($path){
		return $this->globAll($path."/".self::MODULE_FOLDER_NAME."/*");
	}
	
	private function getAllConnecteurByPath($path){
		return $this->globAll($path."/".self::CONNECTEUR_FOLDER_NAME."/*");
	}
	
	private function getAllConnecteurTypeByPath($path){
		return $this->globAll($path."/".self::CONNECTEUR_TYPE_FOLDER_NAME."/*");
	}

	private function getAllTypeDossierByPath($path){
		return $this->globAll($path."/".self::TYPE_DOSSIER_FOLDER_NAME."/*");
	}

	private function globAll($glob_expression){
		$result = array();
        foreach (glob($glob_expression) as $file_config){
        	if (is_dir($file_config)) {
				$result[] = basename($file_config);
			}
		}
		return $result;
	}

	/**
	 * Permet de mettre dans le path l'ensemble des répertoires connecteurs-type des modules.
	 * Les connecteurs types des modules sont chargés après celui du coeur Pastell (c-à-d on ne peut pas masquer un connecteur-type du coeur Pastell)  
	 */
	public function loadConnecteurType(){
		$include_path = $this->getConnecteurTypeIncludePath();
		if($include_path) {
			set_include_path(get_include_path() . PATH_SEPARATOR . implode(PATH_SEPARATOR, $include_path));
		}
	}

	private function getConnecteurTypeIncludePath(){
		$include_path = $this->memoryCache->fetch(self::PASTELL_CONNECTEUR_TYPE_PATH_CACHE_KEY);
		if ($include_path){
			return $include_path;
		}

		$include_path = [];
		$extensions_path_list = $this->getAllExtensionsPath();
		foreach($extensions_path_list as $extension_path){
			$connecteur_type_path = $extension_path."/".self::CONNECTEUR_TYPE_FOLDER_NAME."/";
			if (file_exists($connecteur_type_path)){
				$include_path[] = $connecteur_type_path;

			}
		}
		$this->memoryCache->store(
			self::PASTELL_CONNECTEUR_TYPE_PATH_CACHE_KEY,
			$include_path,
			$this->cache_ttl_in_seconds
		);

		return $include_path;
	}


	public function getGraphiquePath(){
		return WORKSPACE_PATH . "/extensions_graphe.jpg";
	}


	public function creerGraphe(){
		// Lecture des manifest.yml, Ecriture de extensions-graphe.dot, Création de extensions-graphe.jpg
		// Utilisation de GraphViz (! apt-get install graphviz)
		$type = "jpg"; 
		$file = WORKSPACE_PATH . "/extensions_graphe.dot";
		$file_jpg = $this->getGraphiquePath();

		$color = array(
				"extension" => "lavender",
				"version_ko" => "lightblue2",
				"manque_extension" => "lightblue3",
				"connecteur_type" => "blue4",
				"connecteur" => "darkorchid4",
				"flux" => "deeppink4",
		);
		
		if($fp = @ fopen($file, "w")) {
        	fputs($fp,"digraph G {\n");
        	fputs($fp,"graph [rankdir=LR];\n");
        	fputs($fp,"edge [color=lightskyblue,arrowsize=1]\n"); 
        	fputs($fp,"node [color=".$color["extension"].",fontsize = \"10\",shape=plaintext,style=\"rounded,filled\", width=0.3, height=0.3]\n");
        	if($extension_list = $this->getAll()) {
        		foreach($extension_list as $id_e => $extension) {
        			$extension_id = preg_replace("#[^a-zA-Z0-9._ ]#", "_", $extension['id']);
					if (empty($extension['manifest'])){
						continue;
					}
					$label = $this->graphLabelNoeud($extension_id, $extension, $color);
					fputs($fp,$extension_id."[label=".$label."]\n");
        			foreach($extension['manifest']['extension_needed'] as $extension_needed => $extension_needed_info) {
        				$extension_needed_id = preg_replace("#[^a-zA-Z0-9._ ]#", "_", $extension_needed);
        				fputs($fp,$extension_id."->".$extension_needed_id."\n");
        				if (empty($extension_needed_info['extension_presente'])) {//KO Manque extension
        					fputs($fp,$extension_needed_id."[label=\"".$extension_needed."\", color = ".$color["manque_extension"]."]\n");
        				}
        				elseif (! $extension_needed_info['extension_version_ok']) {//Version KO
        					fputs($fp,$extension_needed_id."[label=\"".$extension_needed."\", color = ".$color["version_ko"]."]\n");
        				}
        			}
        		}
        	}
        	
        	// legende
        	fputs($fp,$this->graphLegende($color));       	
        	
        	fputs($fp,"}");      
        	fclose($fp);
        	
        	exec("dot -T$type -o$file_jpg $file", $output, $return_var);
		}
		return $file_jpg;
	}
	
	private function graphLabelNoeud($extension_id, $extension, $color){
		
		$extension_nom = preg_replace("#[^a-zA-Z0-9._ ]#", "_", $extension['nom']);		
		$label = '< <TABLE BORDER="0" CELLBORDER="0" CELLSPACING="0">';		
		$label .= '<TR><TD COLSPAN="2">'.$extension_nom.' ('.$extension_id.')</TD></TR>';
		
		foreach($extension['connecteur-type'] as $connecteur_type) {
			$connecteur_type = preg_replace("#[^a-zA-Z0-9._ ]#", "_", $connecteur_type);
			$label .= '<TR><TD ALIGN="right"><FONT COLOR="'.$color["connecteur_type"].'">Connecteur-type</FONT></TD>';			
			$label .= '<TD ALIGN="left"><FONT COLOR="'.$color["connecteur_type"].'">'.$connecteur_type.'</FONT></TD></TR>';				
		}
		
		foreach($extension['connecteur'] as $connecteur) {
			$connecteur = preg_replace("#[^a-zA-Z0-9._ ]#", "_", $connecteur);
			$label .= '<TR><TD ALIGN="right"><FONT COLOR="'.$color["connecteur"].'">Connecteur</FONT></TD>';
			$label .= '<TD ALIGN="left"><FONT COLOR="'.$color["connecteur"].'">'.$connecteur.'</FONT></TD></TR>';
		}
		
		foreach($extension['flux'] as $flux) {
			$flux = preg_replace("#[^a-zA-Z0-9._ ]#", "_", $flux);
			$label .= '<TR><TD ALIGN="right"><FONT COLOR="'.$color["flux"].'">Flux</FONT></TD>';
			$label .= '<TD ALIGN="left"><FONT COLOR="'.$color["flux"].'">'.$flux.'</FONT></TD></TR>';
		}

		$label .= '</TABLE>>';

		return $label;
	}
	
	private function graphLegende($color){

		$label_noeud = '< <TABLE BORDER="0" CELLBORDER="0" CELLSPACING="0">';
		$label_noeud .= '<TR><TD COLSPAN="2">Extension</TD></TR>';
		$label_noeud .= '<TR><TD ALIGN="right"><FONT COLOR="'.$color["connecteur_type"].'">Connecteur-type</FONT></TD></TR>';
		$label_noeud .= '<TR><TD ALIGN="right"><FONT COLOR="'.$color["connecteur"].'">Connecteur</FONT></TD></TR>';
		$label_noeud .= '<TR><TD ALIGN="right"><FONT COLOR="'.$color["flux"].'">Flux</FONT></TD></TR>';		
		$label_noeud .= '</TABLE>>';	
		
		$cluster = "subgraph cluster_legende {\n";
		$cluster .= "label = \"Légende\"\n";
		$cluster .= "style = \"rounded, filled\"\n";
		$cluster .= "color = lavender\n";
		$cluster .= "fontsize = 10\n";
		$cluster .= "fillcolor = gray100\n";
		$cluster .= "E1[label=".$label_noeud."]\n";
		$cluster .= "E2[label=".$label_noeud."]\n";
		$cluster .= "V[label=\"Extension attendue en version incorrecte\", color = ".$color["version_ko"]."]\n";		
		$cluster .= "M[label=\"Extension attendue manquante\", color = ".$color["manque_extension"]."]\n";
		$cluster .= "E1->E2[label=\"dépend de\" ,fontsize = \"10\"]\n";
		$cluster .= "E1->V\n";
		$cluster .= "E1->M\n";		
		$cluster .= "}\n";	
		return $cluster;
		
	}
}