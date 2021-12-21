<?php

class TypeDossierTransformationEtape implements TypeDossierEtapeSetSpecificInformation
{
    public function setSpecificInformation(TypeDossierEtapeProperties $typeDossierEtape, array $result, StringMapper $stringMapper): array
    {
        $transformation_error = $stringMapper->get('transformation-error');
        $result[DocumentType::ACTION]['supression'][Action::ACTION_RULE][Action::ACTION_RULE_LAST_ACTION][] = $transformation_error;
        return $result;
    }
}
