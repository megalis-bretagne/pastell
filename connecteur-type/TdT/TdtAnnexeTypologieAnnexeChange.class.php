<?php

class TdtAnnexeTypologieAnnexeChange extends ConnecteurTypeActionExecutor {

	public function go(){
		$type_pj_element = $this->getMappingValue('type_pj');
		$type_piece_fichier_element = $this->getMappingValue('type_piece_fichier');
		$type_piece_element = $this->getMappingValue('type_piece');

		if (! $this->getDonneesFormulaire()->get($type_pj_element)){
			return true;
		}

		$type_piece_fichier = $this->getDonneesFormulaire()->getFileContent($type_piece_fichier_element);
		if (! $type_piece_fichier){
			return false;
		}

		foreach(json_decode($type_piece_fichier,true) as $file_info){
			$type_fichier_array[$file_info['filename']][] = $file_info['typologie'];
		}

		$type_pj = [];
		foreach($this->getDonneesFormulaire()->get('autre_document_attache') as $annexe_name){

			if (empty($type_fichier_array[$annexe_name])){
				$type_pj[]="";
				continue;
			}
			$filename = array_shift($type_fichier_array[$annexe_name]);
			preg_match("#\((.{5})\)$#",$filename,$matches);
			$type_pj[] = $matches[1];

		}

		$this->getDonneesFormulaire()->removeFile($type_piece_fichier_element);
		$this->getDonneesFormulaire()->deleteField($type_piece_element);
		$this->getDonneesFormulaire()->setData($type_pj_element,json_encode($type_pj));

		$this->setLastMessage("Modification des fichiers ou de la nature: merci de revoir la typologie");
		return false;
	}

}