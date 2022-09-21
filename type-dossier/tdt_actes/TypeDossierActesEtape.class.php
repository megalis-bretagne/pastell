<?php

class TypeDossierActesEtape implements TypeDossierEtapeSetSpecificInformation
{
    public const FICHIER_ACTE = 'fichier_acte';
    public const ARRETE = 'arrete';
    public const FICHIER_ANNEXE = 'fichier_annexe';
    public const AUTRE_DOCUMENT_ATTACHE = 'autre_document_attache';
    public const OBJET_ACTE = 'objet_acte';
    public const DROIT_SPECIFIQUE = "droit_specifique";

    public const DROIT_SPECIFIQUE_TELETRANSMETTRE = 'teletransmettre';

    public function setSpecificInformation(
        TypeDossierEtapeProperties $typeDossierEtape,
        array $result,
        StringMapper $stringMapper
    ): array {
        $type_piece_action = $stringMapper->get('type-piece');
        $send_tdt = $stringMapper->get('send-tdt');
        $verif_tdt = $stringMapper->get('verif-tdt');
        $annuler_tdt = $stringMapper->get('annuler-tdt');
        $tamponner_tdt = $stringMapper->get('tamponner-tdt');
        $teletransmission_tdt = $stringMapper->get('teletransmission-tdt');
        $typologyChangeByApi = $stringMapper->get('typology-change-by-api');
        $acquiter_tdt = $stringMapper->get('acquiter-tdt');

        if (!empty($typeDossierEtape->specific_type_info[self::FICHIER_ACTE])) {
            $result[DocumentType::ACTION][$type_piece_action][Action::CONNECTEUR_TYPE_MAPPING][self::ARRETE] = $typeDossierEtape->specific_type_info[self::FICHIER_ACTE];
            $result[DocumentType::ACTION][$send_tdt][Action::CONNECTEUR_TYPE_MAPPING][self::ARRETE] = $typeDossierEtape->specific_type_info[self::FICHIER_ACTE];
            $result[DocumentType::ACTION][$verif_tdt][Action::CONNECTEUR_TYPE_MAPPING][self::ARRETE] = $typeDossierEtape->specific_type_info[self::FICHIER_ACTE];
            $result[DocumentType::ACTION][$tamponner_tdt][Action::CONNECTEUR_TYPE_MAPPING][self::ARRETE] = $typeDossierEtape->specific_type_info[self::FICHIER_ACTE];
            $result[DocumentType::ACTION][$typologyChangeByApi][Action::CONNECTEUR_TYPE_MAPPING][self::ARRETE] = $typeDossierEtape->specific_type_info[self::FICHIER_ACTE];
        }
        if (!empty($typeDossierEtape->specific_type_info[self::FICHIER_ANNEXE])) {
            $result[DocumentType::ACTION][$type_piece_action][Action::CONNECTEUR_TYPE_MAPPING][self::AUTRE_DOCUMENT_ATTACHE] = $typeDossierEtape->specific_type_info[self::FICHIER_ANNEXE];
            $result[DocumentType::ACTION][$send_tdt][Action::CONNECTEUR_TYPE_MAPPING][self::AUTRE_DOCUMENT_ATTACHE] = $typeDossierEtape->specific_type_info[self::FICHIER_ANNEXE];
            $result[DocumentType::ACTION][$verif_tdt][Action::CONNECTEUR_TYPE_MAPPING][self::AUTRE_DOCUMENT_ATTACHE] = $typeDossierEtape->specific_type_info[self::FICHIER_ANNEXE];
            $result[DocumentType::ACTION][$tamponner_tdt][Action::CONNECTEUR_TYPE_MAPPING][self::AUTRE_DOCUMENT_ATTACHE] = $typeDossierEtape->specific_type_info[self::FICHIER_ANNEXE];
            $result[DocumentType::ACTION][$typologyChangeByApi][Action::CONNECTEUR_TYPE_MAPPING][self::AUTRE_DOCUMENT_ATTACHE] = $typeDossierEtape->specific_type_info[self::FICHIER_ANNEXE];
        }
        if (!empty($typeDossierEtape->specific_type_info[self::OBJET_ACTE])) {
            $result[DocumentType::ACTION][$send_tdt][Action::CONNECTEUR_TYPE_MAPPING]['objet'] = $typeDossierEtape->specific_type_info[self::OBJET_ACTE];
            $result[DocumentType::ACTION][$verif_tdt][Action::CONNECTEUR_TYPE_MAPPING]['objet'] = $typeDossierEtape->specific_type_info[self::OBJET_ACTE];
            $result[DocumentType::ACTION][$tamponner_tdt][Action::CONNECTEUR_TYPE_MAPPING]['objet'] = $typeDossierEtape->specific_type_info[self::OBJET_ACTE];
        }
        if (!empty($typeDossierEtape->specific_type_info[self::DROIT_SPECIFIQUE])) {
            $result[DocumentType::ACTION][$teletransmission_tdt][Action::ACTION_RULE][Action::ACTION_RULE_DROIT_ID_U]
                = sprintf('%s:%s', $result['__temporary_id'], self::DROIT_SPECIFIQUE_TELETRANSMETTRE);
        }

        reset($result[DocumentType::FORMULAIRE]);
        $onglet1 = key($result[DocumentType::FORMULAIRE]);

        if (!empty($result[DocumentType::FORMULAIRE][$onglet1][$typeDossierEtape->specific_type_info[self::FICHIER_ANNEXE]])) {
            $result[DocumentType::FORMULAIRE][$onglet1][$typeDossierEtape->specific_type_info[self::FICHIER_ANNEXE]]['onchange'] = 'autre_document_attache-change';
        }

        $result[DocumentType::ACTION]['supression'][Action::ACTION_RULE][Action::ACTION_RULE_LAST_ACTION][] = $annuler_tdt;

        $go = false;
        foreach ($result[DocumentType::ACTION] as $action_id => $action_info) {
            if (! $go && $action_id !== $acquiter_tdt) {
                continue;
            }
            $go = true;

            if (
                isset($action_info[Action::ACTION_AUTOMATIQUE])
                && $action_info[Action::ACTION_AUTOMATIQUE] === TypeDossierTranslator::ORIENTATION
            ) {
                $this->makeEditable($action_id, $result, $stringMapper);
            }
        }

        $this->makeEditable('termine', $result, $stringMapper);
        return $result;
    }

    private function makeEditable(string $action_id, array &$result, StringMapper $stringMapper): void
    {
        $acte_publication_date = $stringMapper->get('acte_publication_date');
        if (empty($result[DocumentType::ACTION][$action_id][Action::EDITABLE_CONTENT])) {
            $result[DocumentType::ACTION][$action_id][Action::EDITABLE_CONTENT] = [];
        }
        $result[DocumentType::ACTION][$action_id][Action::EDITABLE_CONTENT][] = $acte_publication_date;
        $result[DocumentType::ACTION][$action_id][Action::MODIFICATION_NO_CHANGE_ETAT] = true;
        $result[DocumentType::ACTION][Action::MODIFICATION][Action::ACTION_RULE][Action::ACTION_RULE_LAST_ACTION][] =
            $action_id;
    }
}
