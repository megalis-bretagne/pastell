<?php

class TypeDossierHeliosEtape implements TypeDossierEtapeSetSpecificInformation
{
    public function setSpecificInformation(TypeDossierEtapeProperties $typeDossierEtape, array $result, StringMapper $stringMapper): array
    {

        $send_tdt = $stringMapper->get('send-tdt');
        $verif_tdt = $stringMapper->get('verif-tdt');
        $helios_extraction = $stringMapper->get('helios-extraction');

        if (!empty($typeDossierEtape->specific_type_info['fichier_pes'])) {
            $result['action'][$send_tdt]['connecteur-type-mapping']['fichier_pes'] = $typeDossierEtape->specific_type_info['fichier_pes'];
            $result['action'][$verif_tdt]['connecteur-type-mapping']['fichier_pes'] = $typeDossierEtape->specific_type_info['fichier_pes'];
            $result['action'][$helios_extraction]['connecteur-type-mapping']['fichier_pes'] = $typeDossierEtape->specific_type_info['fichier_pes'];

            reset($result['formulaire']);
            $onglet1 = key($result['formulaire']);
            $result['formulaire'][$onglet1][$typeDossierEtape->specific_type_info['fichier_pes']]['visionneuse'] = "PESViewerVisionneuse";
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
