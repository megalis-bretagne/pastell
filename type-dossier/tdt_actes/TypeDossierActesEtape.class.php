<?php

class TypeDossierActesEtape implements TypeDossierEtapeSetSpecificInformation
{

	public function setSpecificInformation(TypeDossierEtapeProperties $typeDossierEtape, array $result, StringMapper $stringMapper): array
	{
		$type_piece_action = $stringMapper->get('type-piece');
		$send_tdt = $stringMapper->get('send-tdt');
		$verif_tdt = $stringMapper->get('verif-tdt');
        $onglet_name = $stringMapper->get('Acte');

		$result['action'][$type_piece_action]['connecteur-type-mapping']['arrete'] = $typeDossierEtape->specific_type_info['fichier_acte'];
		$result['action'][$type_piece_action]['connecteur-type-mapping']['autre_document_attache'] = $typeDossierEtape->specific_type_info['fichier_annexe'];

		$result['action'][$send_tdt]['connecteur-type-mapping']['arrete'] = $typeDossierEtape->specific_type_info['fichier_acte'];
		$result['action'][$send_tdt]['connecteur-type-mapping']['autre_document_attache'] = $typeDossierEtape->specific_type_info['fichier_annexe'];
		$result['action'][$send_tdt]['connecteur-type-mapping']['objet'] = $typeDossierEtape->specific_type_info['objet_acte'];

		$result['action'][$verif_tdt]['connecteur-type-mapping']['arrete'] = $typeDossierEtape->specific_type_info['fichier_acte'];
		$result['action'][$verif_tdt]['connecteur-type-mapping']['autre_document_attache'] = $typeDossierEtape->specific_type_info['fichier_annexe'];
		$result['action'][$verif_tdt]['connecteur-type-mapping']['objet'] = $typeDossierEtape->specific_type_info['objet_acte'];

        if ($typeDossierEtape->requis){
            unset($result['page-condition'][$onglet_name]);
        }

        reset($result['formulaire']);
		$onglet1 = key($result['formulaire']);

		if (!empty($result['formulaire'][$onglet1][$typeDossierEtape->specific_type_info['fichier_annexe']])) {
            $result['formulaire'][$onglet1][$typeDossierEtape->specific_type_info['fichier_annexe']]['onchange'] = 'autre_document_attache-change';
        }

		return $result;
	}
}