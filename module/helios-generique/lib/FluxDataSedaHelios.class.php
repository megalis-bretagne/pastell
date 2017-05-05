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

	public function get_archive_size_ko(){
		$file_size = 0;
		foreach($this->getFileList() as $file){
			$file_size += filesize($this->donneesFormulaire->getFilePath($file));
		}
		$result = round($file_size/1024);
		return $result;
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
		return "Flux comptable PES_ Aller $nature en date du {$info['DteStr']} - {$info['LibelleColBud']} ".
			"({$info['CodCol']}{$info['CodBud']})";
	}

	public function get_date_ack_iso_8601(){
		$info = $this->getInfoFromPesRetour();
		return date("Y-m-d",strtotime($info['DteStr']));
	}

	public function get_date_debut_iso_8601(){
		$info = $this->getInfoFromPesAller();
		return date("Y-m-d",strtotime($info['DteStr']));
	}

	public function get_date_integ_iso_8601(){
		$info = $this->getInfoFromPesAller();
		return date("Y-m-d",strtotime($info['DteStr']));
	}

	public function get_name_pes_aller(){
		$info = $this->getInfoFromPesAller();
		$name = $info['Id']?:$info['nomFic'];
		return "Flux PES_Aller $name";
	}

	public function get_date_mandatement(){
		$info = $this->getInfoFromPesAller();
		return date('c',strtotime($info['DteStr']));
	}

	public function get_date_acquittement_iso_8601(){
		$info = $this->getInfoFromPesRetour();
		return date('c',strtotime($info['DteStr']));
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
			$info[$nodeName] = strval($node['V']);
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