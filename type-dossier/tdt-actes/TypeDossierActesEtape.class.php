<?php

class TypeDossierActesEtape implements TypeDossierEtapeSetSpecificInformation
{

	public function setSpecificInformation(TypeDossierEtape $typeDossierEtape, array $result): array
	{
		$result['action']['type-piece']['connecteur-type-mapping']['arrete'] = $typeDossierEtape->specific_type_info['fichier_acte'];
		$result['action']['type-piece']['connecteur-type-mapping']['autre_document_attache'] = $typeDossierEtape->specific_type_info['fichier_annexe'];

		$result['action']['send-tdt']['connecteur-type-mapping']['arrete'] = $typeDossierEtape->specific_type_info['fichier_acte'];
		$result['action']['send-tdt']['connecteur-type-mapping']['autre_document_attache'] = $typeDossierEtape->specific_type_info['fichier_annexe'];
		$result['action']['send-tdt']['connecteur-type-mapping']['objet'] = $typeDossierEtape->specific_type_info['objet_acte'];

		$result['action']['verif-tdt']['connecteur-type-mapping']['arrete'] = $typeDossierEtape->specific_type_info['fichier_acte'];
		$result['action']['verif-tdt']['connecteur-type-mapping']['autre_document_attache'] = $typeDossierEtape->specific_type_info['fichier_annexe'];
		$result['action']['verif-tdt']['connecteur-type-mapping']['objet'] = $typeDossierEtape->specific_type_info['objet_acte'];

		return $result;
	}
}