<?php

class TypeDossierHeliosEtape implements TypeDossierEtapeSetSpecificInformation
{

	public function setSpecificInformation(TypeDossierEtape $typeDossierEtape, array $result): array
	{
		$result['action']['send-tdt']['connecteur-type-mapping']['fichier_pes'] = $typeDossierEtape->specific_type_info['fichier_pes'];
		$result['action']['verif-tdt']['connecteur-type-mapping']['fichier_pes'] = $typeDossierEtape->specific_type_info['fichier_pes'];
		$result['action']['helios-extraction']['connecteur-type-mapping']['fichier_pes'] = $typeDossierEtape->specific_type_info['fichier_pes'];

		if ($typeDossierEtape->specific_type_info['ajout_champs_affiche']){
			$result['champs-affiches'] = array_merge($result['champs-affiches'],['dte_str', 'cod_bud', 'pes_etat_ack']);
			$result['champs-recherche-avancee'] = array_merge(
				$result['champs-recherche-avancee'],
				['id_coll','dte_str', 'cod_bud', 'exercice','id_bordereau','id_pj','pes_etat_ack']
			);
		}


		return $result;
	}
}