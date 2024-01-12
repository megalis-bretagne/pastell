<?php

class TypeDossierHeliosEtape implements TypeDossierEtapeSetSpecificInformation
{
    public const FICHIER_PES = 'fichier_pes';
    public const MAX_SIZE_PES = '128000000';
    public const VISIONNEUSE_PES = 'PESViewerVisionneuse';

    public function setSpecificInformation(
        TypeDossierEtapeProperties $typeDossierEtape,
        array $result,
        StringMapper $stringMapper
    ): array {

        $sendTdtAction = $stringMapper->get('send-tdt');
        $verifTdtAction = $stringMapper->get('verif-tdt');
        $heliosExtraction = $stringMapper->get('helios-extraction');

        if (!empty($typeDossierEtape->specific_type_info['fichier_pes'])) {
            $result[DocumentType::ACTION][$sendTdtAction][Action::CONNECTEUR_TYPE_MAPPING][self::FICHIER_PES]
                = $typeDossierEtape->specific_type_info[self::FICHIER_PES];
            $result[DocumentType::ACTION][$verifTdtAction][Action::CONNECTEUR_TYPE_MAPPING][self::FICHIER_PES]
                = $typeDossierEtape->specific_type_info[self::FICHIER_PES];
            $result[DocumentType::ACTION][$heliosExtraction][Action::CONNECTEUR_TYPE_MAPPING][self::FICHIER_PES]
                = $typeDossierEtape->specific_type_info[self::FICHIER_PES];

            reset($result[DocumentType::FORMULAIRE]);
            $onglet1 = key($result[DocumentType::FORMULAIRE]);
            $result[DocumentType::FORMULAIRE][$onglet1][$typeDossierEtape->specific_type_info[self::FICHIER_PES]]
            ['max_file_size'] = self::MAX_SIZE_PES;
            $result[DocumentType::FORMULAIRE][$onglet1][$typeDossierEtape->specific_type_info[self::FICHIER_PES]]
            ['visionneuse'] = self::VISIONNEUSE_PES;
        }

        if ($typeDossierEtape->specific_type_info['ajout_champs_affiche']) {
            foreach (['dte_str', 'cod_bud', 'pes_etat_ack'] as $champs_id) {
                $result['champs-affiches'][] = $stringMapper->get($champs_id);
            }
            foreach (['id_coll','dte_str', 'cod_bud', 'exercice','id_bordereau','id_pj','pes_etat_ack'] as $champs_id) {
                $result['champs-recherche-avancee'][] = $stringMapper->get($champs_id);
            }
        }
        return $result;
    }
}
