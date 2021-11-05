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
        $teletransmission_tdt = $stringMapper->get('teletransmission-tdt');
        $typologyChangeByApi = $stringMapper->get('typology-change-by-api');

        if (!empty($typeDossierEtape->specific_type_info[self::FICHIER_ACTE])) {
            $result[DocumentType::ACTION][$type_piece_action][Action::CONNECTEUR_TYPE_MAPPING][self::ARRETE] = $typeDossierEtape->specific_type_info[self::FICHIER_ACTE];
            $result[DocumentType::ACTION][$send_tdt][Action::CONNECTEUR_TYPE_MAPPING][self::ARRETE] = $typeDossierEtape->specific_type_info[self::FICHIER_ACTE];
            $result[DocumentType::ACTION][$verif_tdt][Action::CONNECTEUR_TYPE_MAPPING][self::ARRETE] = $typeDossierEtape->specific_type_info[self::FICHIER_ACTE];
            $result[DocumentType::ACTION][$typologyChangeByApi][Action::CONNECTEUR_TYPE_MAPPING][self::ARRETE] = $typeDossierEtape->specific_type_info[self::FICHIER_ACTE];
        }
        if (!empty($typeDossierEtape->specific_type_info[self::FICHIER_ANNEXE])) {
            $result[DocumentType::ACTION][$type_piece_action][Action::CONNECTEUR_TYPE_MAPPING][self::AUTRE_DOCUMENT_ATTACHE] = $typeDossierEtape->specific_type_info[self::FICHIER_ANNEXE];
            $result[DocumentType::ACTION][$send_tdt][Action::CONNECTEUR_TYPE_MAPPING][self::AUTRE_DOCUMENT_ATTACHE] = $typeDossierEtape->specific_type_info[self::FICHIER_ANNEXE];
            $result[DocumentType::ACTION][$verif_tdt][Action::CONNECTEUR_TYPE_MAPPING][self::AUTRE_DOCUMENT_ATTACHE] = $typeDossierEtape->specific_type_info[self::FICHIER_ANNEXE];
            $result[DocumentType::ACTION][$typologyChangeByApi][Action::CONNECTEUR_TYPE_MAPPING][self::AUTRE_DOCUMENT_ATTACHE] = $typeDossierEtape->specific_type_info[self::FICHIER_ANNEXE];
        }
        if (!empty($typeDossierEtape->specific_type_info[self::OBJET_ACTE])) {
            $result[DocumentType::ACTION][$send_tdt][Action::CONNECTEUR_TYPE_MAPPING]['objet'] = $typeDossierEtape->specific_type_info[self::OBJET_ACTE];
            $result[DocumentType::ACTION][$verif_tdt][Action::CONNECTEUR_TYPE_MAPPING]['objet'] = $typeDossierEtape->specific_type_info[self::OBJET_ACTE];
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

        return $result;
    }
}
