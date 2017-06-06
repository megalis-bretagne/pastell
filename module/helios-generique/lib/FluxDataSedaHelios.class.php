<?php


class FluxDataSedaHelios extends FluxDataStandard {

	private $info_from_pes_aller;
	private $info_from_pes_retour;
	
	public function getData($key) {
		$method = "get_$key";
		if (method_exists($this, $method)){
			return $this->$method($key);
		}
		return parent::getData($key);
	}

	/**
	 * @return string Contenu de la balise NomFic du fichier PES_ALLER
	 */
	public function get_nomFic(){
		return $this->getSpecificInfoFromPesAller('nomFic');
	}

	/**
	 * @return string empreinte sha1 du contenu du fichier PES_ALLER
	 */
	public function get_pesAller_sha1(){
		return sha1($this->donneesFormulaire->getFileContent('fichier_pes'));
	}

	/**
	 * @return string Contenu de la balise LibelleCodBud
	 */
	public function get_LibelleColBud(){
		return $this->getSpecificInfoFromPesAller('LibelleColBud');
	}

	/**
	 * @return string Domaine (recette, dépense, pj)
	 */
	public function get_Domaine(){
		$info = $this->getInfoFromPesAller();
		$result = array();
		if ($info['is_recette']){
			$result[] = "PES_RecetteAller";
		}
		if ($info['is_depense']){
			$result[] = "PES_DepenseAller";
		}
		if ($info['is_facture']){
			$result[] = "PES_Facture";
		}
		if (! $result && $info['is_pj']){
			$result[] = "PES_PJ";
		}
		return utf8_encode(implode(", ", $result));
	}

	public function get_IdPost(){
		return $this->getSpecificInfoFromPesAller('IdPost');
	}

	public function get_IdColl(){
		return $this->getSpecificInfoFromPesAller('IdColl');
	}

	public function get_CodBud(){
		return $this->getSpecificInfoFromPesAller('CodBud');
	}

	public function get_date_mandatement_iso_8601(){
		$info = $this->getInfoFromPesAller();
		return date("c",strtotime($info['DteStr']));
	}

	public function get_date_mandatement(){
		$info = $this->getInfoFromPesAller();
		return date('Y-m-d',strtotime($info['DteStr']));
	}

	public function get_IdBord_IdPce(){
		$result = array();
		$info = $this->getInfoFromPesAller();
		foreach($info['id_bord'] as $id_bord){
			$result[] = "IdBord $id_bord";
		}
		foreach($info['id_piece'] as $id_pce){
			$result[] = "IdPce $id_pce";
		}

		return $result;
	}

	private function getSpecificInfoFromPesAller($key){
		$info = $this->getInfoFromPesAller();
		return $info[$key];
	}

	public function get_archive_size_ko(){
		$file_size = 0;
		foreach($this->getFileList() as $file){
			$file_size += filesize($this->donneesFormulaire->getFilePath($file));
		}
		$result = round($file_size/1024);
		return $result;
	}

	public function get_pes_aller_size_in_byte(){
		return filesize($this->donneesFormulaire->getFilePath('fichier_pes'));
	}

	public function get_start_date(){
		$info = $this->getInfoFromPesRetour();
		return $info['DteStr'];
	}

	public function get_CodcolCodbud(){
		$info = $this->getInfoFromPesAller();
		return $info['CodCol'].$info['CodBud'];
	}

	public function get_nom_megalis(){
		$info = $this->getInfoFromPesAller();
		$result = array();
		foreach(
			array(
				'is_recette'=>"recettes",
				"is_depense"=>"dépenses",
				'is_pj'=>'pièces justificatives',
				'is_facture'=>'facture'
			) as $id=>$libelle){
			if ($info[$id]){
				$result[] = $libelle;
			}
		}
		$nature = implode(' - ',$result);
		return utf8_encode("Flux comptable PES_ Aller $nature en date du {$info['DteStr']} - {$info['LibelleColBud']} ".
			"({$info['CodCol']}{$info['CodBud']})");
	}

	public function get_date_ack_iso_8601(){
		$info = $this->getInfoFromPesRetour();
		return date("c",strtotime($info['DteStr']));
	}

	public function get_date_debut_iso_8601(){
		$info = $this->getInfoFromPesAller();
		return date("Y-m-d",strtotime($info['DteStr']));
	}



	public function get_name_pes_aller(){
		$info = $this->getInfoFromPesAller();
		$name = $info['Id']?:$info['nomFic'];
		return "Flux PES_Aller $name";
	}

	public function get_date_acquittement_iso_8601(){
		$info = $this->getInfoFromPesRetour();
		return date('c',strtotime($info['DteStr']));
	}

	public function get_date_acquittement(){
		$info = $this->getInfoFromPesRetour();
		return date('Y-m-d',strtotime($info['DteStr']));
	}

	private function getInfoFromPesAller(){
		if (! $this->info_from_pes_aller){
			$pes_aller = $this->donneesFormulaire->getFileContent('fichier_pes');
			$this->info_from_pes_aller = $this->extractInfoFromPESAller($pes_aller);
		}
		return $this->info_from_pes_aller;
	}

	public function getInfoFromPesRetour(){
		if (! $this->info_from_pes_retour){
			$pes_retour = $this->donneesFormulaire->getFilePath('fichier_reponse');
			$this->info_from_pes_retour = $this->extractInfoFromPESRetour($pes_retour);
		}
		return $this->info_from_pes_retour;
	}

	private function extractInfoFromPESAller($pes_aller_content){
		$xml =  simplexml_load_string($pes_aller_content);

		$info = array();
		$info['nomFic'] =  strval($xml->Enveloppe->Parametres->NomFic['V']);
		$info['Id'] = $xml['Id'];

		$info['is_recette'] = isset($xml->PES_RecetteAller);
		$info['is_depense'] = isset($xml->PES_DepenseAller);
		$info['is_pj'] = isset($xml->PES_PJ);
		$info['is_facture'] = isset($xml->PES_Facture);

		foreach(array('IdPost','DteStr','IdColl','CodCol','CodBud','LibelleColBud') as $nodeName) {
			$node = $xml->EnTetePES->$nodeName;
			$info[$nodeName] = utf8_decode(strval($node['V']));
		}

		$info['id_bord'] = array();
		foreach(array('PES_RecetteAller','PES_DepenseAller') as $pes_Aller){
			if (! isset($xml->$pes_Aller)){
				continue;
			}
			foreach($xml->$pes_Aller->Bordereau as $bordereau){
				$info['id_bord'][] = strval($bordereau->BlocBordereau->IdBord['V']);
				foreach($bordereau->Piece as $j => $piece){
					if($piece->BlocPiece->InfoPce->IdPce['V'] != null) {
						$info['id_piece'][] = strval($piece->BlocPiece->InfoPce->IdPce['V']);
					}
					else {
						$info['id_piece'][] = strval($piece->BlocPiece->IdPce['V']);
					}
				}
			}
		}

		return $info;
	}

	private function extractInfoFromPESRetour($pes_retour){
		$pes_retour_content = file_get_contents($pes_retour);
		$xml =  simplexml_load_string($pes_retour_content);

		$info = array();
		$info['DteStr'] =  strval($xml->EnTetePES->DteStr['V']);

		return $info;
	}

}