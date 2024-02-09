<?php

class TypeDossierCfnEtape implements TypeDossierEtapeSetSpecificInformation
{
    use TypeDossierRemoveFromEditableContent;

    public function setSpecificInformation(
        TypeDossierEtapeProperties $typeDossierEtape,
        array $result,
        StringMapper $stringMapper
    ): array {
        foreach (
            [
                'archive_bp' => 'archive_bp',
                'fichier_de_description' => 'fichier_de_description',
            ] as $mapping_key => $specific_key
        ) {
            if (!empty($typeDossierEtape->specific_type_info[$specific_key])) {
                $result[DocumentType::ACTION][$stringMapper->get('send-cfn')]
                [Action::CONNECTEUR_TYPE_MAPPING][$mapping_key] = $typeDossierEtape->specific_type_info[$specific_key];
            }
        }
        return $result;
    }
}
