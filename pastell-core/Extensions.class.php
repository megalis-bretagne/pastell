<?php 
class Extensions {
	
	const MODULE_FOLDER_NAME = "module";
	const CONNECTEUR_FOLDER_NAME = "connecteur";
	const CONNECTEUR_TYPE_FOLDER_NAME = "connecteur-type";
	
	private $extensionSQL;
	private $manifestFactory;
	private $pastell_path;
	
	/**
	 * 
	 * @param ExtensionSQL $extensionSQL
	 * @param ManifestFactory $manifestFactory 
	 * @param String $pastell_path racine des fichiers Pastell
	 */
	public function __construct(ExtensionSQL $extensionSQL, ManifestFactory $manifestFactory,$pastell_path){
		$this->extensionSQL = $extensionSQL;
		$this->manifestFactory = $manifestFactory;
		$this->pastell_path = $pastell_path;
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
	
	
	public function getAllConnecteur(){
		$result = array();
		foreach($this->getAllExtensionsPath() as $search){
			foreach($this->getAllConnecteurByPath($search) as $id_connecteur){
				$result[$id_connecteur] = $search."/".self::CONNECTEUR_FOLDER_NAME."/$id_connecteur";
			}
		}
		return $result;
	}
	
	public function getConnecteurPath($id_connecteur){
		$result = $this->getAllConnecteur();
		if (empty($result[$id_connecteur])){
			return false;
		}
		return $result[$id_connecteur];
	}
	
	
	private function getAllExtensionsPath(){
		$to_search = array($this->pastell_path);
		foreach($this->extensionSQL->getAll() as $extension){
			$to_search[] = $extension['path'];
		}
		return $to_search;
	}
	
	public function getAllModule(){
		$result = array();
		foreach($this->getAllExtensionsPath() as $search){
			foreach($this->getAllModuleByPath($search) as $id_module){
				$result[$id_module] = $search."/".self::MODULE_FOLDER_NAME."/$id_module";
			}
		}
		return $result;
	}
	
	public function getModulePath($id_module_to_found){
		$result = $this->getAllModule();
		if (empty($result[$id_module_to_found])){
			return false;
		}
		return $result[$id_module_to_found];
	}

	public function getInfo($id_e){
		$info = $this->extensionSQL->getInfo($id_e);
		$info = $this->getInfoFromPath($info['path']);
		$info['error'] = false;
		$info['warning'] = false;
		$info['pastell-version-ok'] = true;
		
		$info['id_e'] = $id_e;
		if (! file_exists($info['path'])){
			$info['error'] = "Extension non-trouv�";
			$info['error-detail'] = "L'emplacement {$info['path']} n'a pas �t� trouv� sur le syst�me de fichier";
			return $info;
		} 
		if (! $info['manifest']['nom']){
			$info['warning'] = "manifest.yml absent";
			$info['warning-detail'] = "Le fichier manifest.yml n'a pas �t� trouv� dans {$info['path']}";
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
			$info['warning-detail'] = "Cette extension d�pend d'autres extensions qui ne sont pas install�s sur cette instance de Pastell : " . implode(', ',$extension_absente);
			return $info;
		} 
		if ($extension_bad_version){
			$info['warning'] = "Mauvais num�ro de version d'une d�pendance";
			$info['warning-detail'] = "Ce extension d�pend d'autres extensions qui ne sont pas dans une version attendue : " . implode(', ',$extension_bad_version);
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
		$extensions_list = array();
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
	
	private function globAll($glob_expression){
		$result = array();
		foreach (glob($glob_expression) as $file_config){			
			$result[] =  basename($file_config);
		}
		return $result;
	}

	/**
	 * Permet de mettre dans le path l'ensemble des r�pertoires connecteurs-type des modules.
	 * Les connecteurs types des modules sont charg�s apr�s celui du coeur Pastell (c-�-d on ne peut pas masquer un connecteur-type du coeur Pastell)  
	 */
	public function loadConnecteurType(){
		$extensions_path_list = $this->getAllExtensionsPath();
		foreach($extensions_path_list as $extension_path){
			$connecteur_type_path = $extension_path."/".self::CONNECTEUR_TYPE_FOLDER_NAME."/"; 
			if (file_exists($connecteur_type_path)){
				set_include_path(get_include_path() . PATH_SEPARATOR . $connecteur_type_path);
			}
		}
	}

	public function creerGraphe(){
		// Lecture des manifest.yml, Ecriture de extensions-graphe.dot, Cr�ation de extensions-graphe.jpg
		// Utilisation de GraphViz (! apt-get install graphviz)
		$type = "jpg"; 
		$file = PASTELL_PATH."web/extension/extensions_graphe/extensions_graphe.dot";
		$file_jpg = PASTELL_PATH."web/extension/extensions_graphe/extensions_graphe.jpg";
		$extension_id = "";
		$extension_needed_id = "";

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
					//$href = "system/extension.php?id_extension=".$id_e;
					//fputs($fp,$extension_id."[href=\"".$href."\",label=".$label."]\n");
					fputs($fp,$extension_id."[label=".$label."]\n");
        			foreach($extension['manifest']['extension_needed'] as $extension_needed => $extension_needed_info) {
        				$extension_needed_id = preg_replace("#[^a-zA-Z0-9._ ]#", "_", $extension_needed);
        				fputs($fp,$extension_id."->".$extension_needed_id."\n");
        				if (! $extension_needed_info['extension_presente']) {//KO Manque extension
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
		$cluster .= "label = \"L�gende\"\n";
		$cluster .= "style = \"rounded, filled\"\n";
		$cluster .= "color = lavender\n";
		$cluster .= "fontsize = 10\n";
		$cluster .= "fillcolor = gray100\n";
		$cluster .= "E1[label=".$label_noeud."]\n";
		$cluster .= "E2[label=".$label_noeud."]\n";
		$cluster .= "V[label=\"Extension attendue en version incorrecte\", color = ".$color["version_ko"]."]\n";		
		$cluster .= "M[label=\"Extension attendue manquante\", color = ".$color["manque_extension"]."]\n";
		$cluster .= "E1->E2[label=\"d�pend de\" ,fontsize = \"10\"]\n";
		$cluster .= "E1->V\n";
		$cluster .= "E1->M\n";		
		$cluster .= "}\n";	
		return $cluster;
		
	}
}