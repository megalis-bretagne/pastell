<?php

class TypeDossierSignatureEtape implements TypeDossierEtapeSetSpecificInformation {

	public function setSpecificInformation(TypeDossierEtape $typeDossierEtape,array $result) : array{

		if (empty($typeDossierEtape->specific_type_info['has-date-limite'])){
			unset($result['formulaire']['i-Parapheur']['has_date_limite']);
			unset($result['formulaire']['i-Parapheur']['date_limite']);
		} else {
			$result['action']['send-iparapheur']['connecteur-type-mapping']['iparapheur_has_date_limite'] = 'has_date_limite';
			$result['action']['send-iparapheur']['connecteur-type-mapping']['iparapheur_date_limite'] = 'date_limite';
		}

		if (empty($typeDossierEtape->specific_type_info['has-metadata-in-json'])){
			unset($result['formulaire']['i-Parapheur']['json_metadata']);
		}

		if ($typeDossierEtape->requis){
			unset($result['page-condition']['i-Parapheur']);
		}

		foreach(['objet'=>'libelle_parapheur','document'=>'document_a_signer','autre_document_attache'=>'annexe'] as $mapping_key => $specific_key) {
			if (!empty($typeDossierEtape->specific_type_info[$specific_key])) {
				$result['action']['send-iparapheur']['connecteur-type-mapping'][$mapping_key] = $typeDossierEtape->specific_type_info[$specific_key];
			}
		}
		return $result;
	}


}