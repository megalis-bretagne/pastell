<?php

class TypeDossierSignatureEtape implements TypeDossierEtapeSetSpecificInformation {

	public function setSpecificInformation(TypeDossierEtapeProperties $typeDossierEtape, array $result, StringMapper $stringMapper) : array{
		$onglet_name = $stringMapper->get('i-Parapheur');
		$send_iparapheur_action = $stringMapper->get('send-iparapheur');
		$verif_iparapheur_action = $stringMapper->get('verif-iparapheur');
		$has_date_limite_element = $stringMapper->get("has_date_limite");
		$date_limite_element = $stringMapper->get("date_limite");
		$json_metadata_element = $stringMapper->get("json_metadata");


        if (empty($result['action'][$send_iparapheur_action]['connecteur-type-mapping'])) {
            $result['action'][$send_iparapheur_action]['connecteur-type-mapping'] = [];
        }

        if (empty($result['action'][$verif_iparapheur_action]['connecteur-type-mapping'])) {
            $result['action'][$verif_iparapheur_action]['connecteur-type-mapping'] = [];
        }

		if (empty($typeDossierEtape->specific_type_info['has-date-limite'])){
			unset($result['formulaire'][$onglet_name][$has_date_limite_element]);
			unset($result['formulaire'][$onglet_name][$date_limite_element]);
		} else {
			$result['action'][$send_iparapheur_action]['connecteur-type-mapping']['iparapheur_has_date_limite'] = $has_date_limite_element;
			$result['action'][$send_iparapheur_action]['connecteur-type-mapping']['iparapheur_date_limite'] = $date_limite_element;
            $result['action'][$verif_iparapheur_action]['connecteur-type-mapping']['iparapheur_has_date_limite'] = $has_date_limite_element;
            $result['action'][$verif_iparapheur_action]['connecteur-type-mapping']['iparapheur_date_limite'] = $date_limite_element;
		}

		if (empty($typeDossierEtape->specific_type_info['has-metadata-in-json'])){
			unset($result['formulaire'][$onglet_name][$json_metadata_element]);
			unset($result['action'][$send_iparapheur_action]['connecteur-type-mapping']['json_metadata']);
		}

		if ($typeDossierEtape->requis){
			unset($result['page-condition'][$onglet_name]);
		}

		foreach(['objet'=>'libelle_parapheur','document'=>'document_a_signer','autre_document_attache'=>'annexe'] as $mapping_key => $specific_key) {
			if (!empty($typeDossierEtape->specific_type_info[$specific_key])) {
				$result['action'][$send_iparapheur_action]['connecteur-type-mapping'][$mapping_key] = $typeDossierEtape->specific_type_info[$specific_key];
                $result['action'][$verif_iparapheur_action]['connecteur-type-mapping'][$mapping_key] = $typeDossierEtape->specific_type_info[$specific_key];
			}
		}
		return $result;
	}


}