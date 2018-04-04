<?php

require_once __DIR__."/ActesTypePJData.class.php";

class ActesTypePJ {

	/**
	 * @param ActesTypePJData $actesTypePJData
	 * @return array
	 * @throws Exception
	 */
	public function getTypePJListe(ActesTypePJData $actesTypePJData){

		$simpleXMLWrapper = new SimpleXMLWrapper();

		$xml = $simpleXMLWrapper->loadFile($actesTypePJData->classification_file_path);

		$all_type = [];
		foreach( $xml->xpath("//actes:TypePJNatureActe") as $type_pj){
			$code = strval($type_pj->xpath("@actes:CodeTypePJ")[0]);
			$libelle = strval($type_pj->xpath("@actes:Libelle")[0]);
			$nature_id = strval($type_pj->xpath("parent::actes:NatureActe/@actes:CodeNatureActe")[0]);
			$matiere1 = $code[0];
			$matiere2 = $code[1];
			$all_type[$nature_id][$matiere1][$matiere2][$code] = $libelle;
		}

		$result = [];

		//Matiere1 + 0
		if (isset($all_type[$actesTypePJData->acte_nature]
			[$actesTypePJData->actes_matiere1][0])
		) {
			$result = array_merge($result,
				$all_type[$actesTypePJData->acte_nature][$actesTypePJData->actes_matiere1][0]
			);
		}

		//Matiere1+Matiere2
		if (isset($all_type[$actesTypePJData->acte_nature]
			[$actesTypePJData->actes_matiere1]
			[$actesTypePJData->actes_matiere2])
		) {
			$result = array_merge($result,
				$all_type[$actesTypePJData->acte_nature]
				[$actesTypePJData->actes_matiere1]
				[$actesTypePJData->actes_matiere2]
			);
		}

		//99_*
		if (isset($all_type[$actesTypePJData->acte_nature][9][9])) {
			$result = array_merge($result,$all_type[$actesTypePJData->acte_nature][9][9]);
		}

		//99_AU
		if (isset($all_type[$actesTypePJData->acte_nature][9][9])) {
			$result = array_merge($result,['99_AU'=>'Autre Document']);
		}

		return $result;
	}

}