<?php 

require_once(__DIR__."/../../actes-generique/action/Classification.class.php");

class ClassificationCDG extends Classification {
	
	public function isEnabled(){
		
		$infoCDG = $this->getEntiteSQL()->getCDG($this->id_e);
		if (! $infoCDG){
			return true;
		}
		
		$file = $this->getFile($this->id_e);
		if (!$file){
			return true;
		}
		
		$donneesFormulaireCDG = $this->getConnecteurFactory()->getConnecteurConfigByType($infoCDG,'actes-cdg','classification-cdg');
		if (!$donneesFormulaireCDG){
			return true;
		}
		
		$classifCDG = $donneesFormulaireCDG->get("classification_cdg");

		if (! $classifCDG){
			return true;
		}
		$file_name = false;
		$num_file = 0;
		foreach($classifCDG as $i => $nom_file){
			if($nom_file == $file){
				$file_name =  $donneesFormulaireCDG->getFilePath('classification_cdg',$i);
				$num_file = $i;
			}
		}
		
		if (! $file_name || ! file_exists($file_name)){
			return true;
		}

		if ($donneesFormulaireCDG->get("classification_a_jour_$num_file")){
			return false;
		} else {
			return true;
		}
	}
	
	
	
	private function getFile($id_e){
		$donneesFormulaire = $this->getConnecteurFactory()->getConnecteurConfigByType($id_e,$this->type,'TdT');
		
		if (! $donneesFormulaire){
			return false;
		}
		
		$file = $donneesFormulaire->get('nomemclature_file');
		
		if ($file){
			return $file;
		}
		
		$allAncetre = $this->getEntiteSQL()->getAncetre($id_e);
		array_pop($allAncetre);
		$allAncetre = array_reverse($allAncetre);

		
		foreach($allAncetre as $ancetre){
			$donneesFormulaireAncetre = $this->getConnecteurFactory()->getConnecteurConfigByType($ancetre['id_e'],$this->type,'TdT');
			
			$file = $donneesFormulaireAncetre->get('nomemclature_file');
			if ($file){
				return $file;
			}
		}
		return false;
	}
	
}
