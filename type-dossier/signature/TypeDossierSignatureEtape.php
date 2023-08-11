<?php

class TypeDossierSignatureEtape implements TypeDossierEtapeSetSpecificInformation
{
    use TypeDossierRemoveFromEditableContent;

    public function setSpecificInformation(
        TypeDossierEtapeProperties $typeDossierEtape,
        array $result,
        StringMapper $stringMapper
    ): array {
        $onglet_name = $stringMapper->get('iparapheur');
        $send_iparapheur_action = $stringMapper->get('send-iparapheur');
        $sendSignatureErrorAction = $stringMapper->get('send-signature-error');
        $verif_iparapheur_action = $stringMapper->get('verif-iparapheur');
        $checkSignatureErrorState = $stringMapper->get('erreur-verif-iparapheur');
        $rejet_iparapheur_action = $stringMapper->get('rejet-iparapheur');
        $has_date_limite_element = $stringMapper->get("has_date_limite");
        $date_limite_element = $stringMapper->get("date_limite");
        $json_metadata_element = $stringMapper->get("json_metadata");
        $continue_after_refusal = $stringMapper->get('continue_after_refusal');

        if (empty($result[DocumentType::ACTION][$send_iparapheur_action][Action::CONNECTEUR_TYPE_MAPPING])) {
            $result[DocumentType::ACTION][$send_iparapheur_action][Action::CONNECTEUR_TYPE_MAPPING] = [];
        }

        if (empty($result[DocumentType::ACTION][$verif_iparapheur_action][Action::CONNECTEUR_TYPE_MAPPING])) {
            $result[DocumentType::ACTION][$verif_iparapheur_action][Action::CONNECTEUR_TYPE_MAPPING] = [];
        }

        if (empty($typeDossierEtape->specific_type_info['has_date_limite'])) {
            unset(
                $result[DocumentType::FORMULAIRE][$onglet_name][$has_date_limite_element],
                $result[DocumentType::FORMULAIRE][$onglet_name][$date_limite_element]
            );
            $this->removeFromEditableContent(
                ['has_date_limite','date_limite'],
                $result
            );
        } else {
            $result[DocumentType::ACTION][$send_iparapheur_action][Action::CONNECTEUR_TYPE_MAPPING]['iparapheur_has_date_limite'] = $has_date_limite_element;
            $result[DocumentType::ACTION][$send_iparapheur_action][Action::CONNECTEUR_TYPE_MAPPING]['iparapheur_date_limite'] = $date_limite_element;
            $result[DocumentType::ACTION][$verif_iparapheur_action][Action::CONNECTEUR_TYPE_MAPPING]['iparapheur_has_date_limite'] = $has_date_limite_element;
            $result[DocumentType::ACTION][$verif_iparapheur_action][Action::CONNECTEUR_TYPE_MAPPING]['iparapheur_date_limite'] = $date_limite_element;
        }

        if (empty($typeDossierEtape->specific_type_info['has_metadata_in_json'])) {
            unset($result[DocumentType::FORMULAIRE][$onglet_name][$json_metadata_element]);
            unset($result[DocumentType::ACTION][$send_iparapheur_action][Action::CONNECTEUR_TYPE_MAPPING]['json_metadata']);
            $this->removeFromEditableContent(
                ['json_metadata'],
                $result
            );
        }

        if (!empty($typeDossierEtape->specific_type_info[$continue_after_refusal])) {
            $result[DocumentType::ACTION][TypeDossierTranslator::ORIENTATION][Action::ACTION_RULE][Action::ACTION_RULE_LAST_ACTION][] = $rejet_iparapheur_action;
            if ($typeDossierEtape->automatique) {
                $result[DocumentType::ACTION][$rejet_iparapheur_action][Action::ACTION_AUTOMATIQUE] = TypeDossierTranslator::ORIENTATION;
            }
        }

        foreach (['objet' => 'libelle_parapheur','document' => 'document_a_signer','autre_document_attache' => 'annexe'] as $mapping_key => $specific_key) {
            if (!empty($typeDossierEtape->specific_type_info[$specific_key])) {
                $result[DocumentType::ACTION][$send_iparapheur_action][Action::CONNECTEUR_TYPE_MAPPING][$mapping_key] = $typeDossierEtape->specific_type_info[$specific_key];
                $result[DocumentType::ACTION][$verif_iparapheur_action][Action::CONNECTEUR_TYPE_MAPPING][$mapping_key] = $typeDossierEtape->specific_type_info[$specific_key];
            }
        }

        $result[DocumentType::ACTION]['supression'][Action::ACTION_RULE]
                [Action::ACTION_RULE_LAST_ACTION][] = $rejet_iparapheur_action;
        $result[DocumentType::ACTION]['supression'][Action::ACTION_RULE]
                [Action::ACTION_RULE_LAST_ACTION][] = $checkSignatureErrorState;
        $result[DocumentType::ACTION][Action::MODIFICATION][Action::ACTION_RULE]
                [Action::ACTION_RULE_LAST_ACTION][] = $sendSignatureErrorAction;

        return $result;
    }
}
