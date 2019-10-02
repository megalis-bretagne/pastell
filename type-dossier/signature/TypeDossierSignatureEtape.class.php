<?php

class TypeDossierSignatureEtape implements TypeDossierEtapeSetSpecificInformation {

	public function setSpecificInformation(TypeDossierEtapeProperties $typeDossierEtape, array $result, StringMapper $stringMapper) : array{
		$onglet_name = $stringMapper->get('i-Parapheur');
		$send_iparapheur_action = $stringMapper->get('send-iparapheur');
		$verif_iparapheur_action = $stringMapper->get('verif-iparapheur');
		$rejet_iparapheur_action = $stringMapper->get('rejet-iparapheur');
		$has_date_limite_element = $stringMapper->get("has_date_limite");
		$date_limite_element = $stringMapper->get("date_limite");
		$json_metadata_element = $stringMapper->get("json_metadata");
		$continue_after_refusal = $stringMapper->get('continue_after_refusal');

        if (empty($result[DocumentType::ACTION][$send_iparapheur_action]['connecteur-type-mapping'])) {
            $result[DocumentType::ACTION][$send_iparapheur_action]['connecteur-type-mapping'] = [];
        }

        if (empty($result[DocumentType::ACTION][$verif_iparapheur_action]['connecteur-type-mapping'])) {
            $result[DocumentType::ACTION][$verif_iparapheur_action]['connecteur-type-mapping'] = [];
        }

		if (empty($typeDossierEtape->specific_type_info['has-date-limite'])){
			unset($result['formulaire'][$onglet_name][$has_date_limite_element]);
			unset($result['formulaire'][$onglet_name][$date_limite_element]);
		} else {
			$result[DocumentType::ACTION][$send_iparapheur_action]['connecteur-type-mapping']['iparapheur_has_date_limite'] = $has_date_limite_element;
			$result[DocumentType::ACTION][$send_iparapheur_action]['connecteur-type-mapping']['iparapheur_date_limite'] = $date_limite_element;
            $result[DocumentType::ACTION][$verif_iparapheur_action]['connecteur-type-mapping']['iparapheur_has_date_limite'] = $has_date_limite_element;
            $result[DocumentType::ACTION][$verif_iparapheur_action]['connecteur-type-mapping']['iparapheur_date_limite'] = $date_limite_element;
		}

		if (empty($typeDossierEtape->specific_type_info['has-metadata-in-json'])){
			unset($result['formulaire'][$onglet_name][$json_metadata_element]);
			unset($result[DocumentType::ACTION][$send_iparapheur_action]['connecteur-type-mapping']['json_metadata']);
		}

		if ($typeDossierEtape->requis){
			unset($result['page-condition'][$onglet_name]);
		}

        if (!empty($typeDossierEtape->specific_type_info[$continue_after_refusal])) {
            $result[DocumentType::ACTION][TypeDossierTranslator::ORIENTATION]['rule']['last-action'][] = $rejet_iparapheur_action;
            if ($typeDossierEtape->automatique) {
                $result[DocumentType::ACTION][$rejet_iparapheur_action]['action-automatique'] = TypeDossierTranslator::ORIENTATION;
            }
        }

        foreach(['objet'=>'libelle_parapheur','document'=>'document_a_signer','autre_document_attache'=>'annexe'] as $mapping_key => $specific_key) {
			if (!empty($typeDossierEtape->specific_type_info[$specific_key])) {
				$result[DocumentType::ACTION][$send_iparapheur_action]['connecteur-type-mapping'][$mapping_key] = $typeDossierEtape->specific_type_info[$specific_key];
                $result[DocumentType::ACTION][$verif_iparapheur_action]['connecteur-type-mapping'][$mapping_key] = $typeDossierEtape->specific_type_info[$specific_key];
			}
		}
		return $result;
	}
}