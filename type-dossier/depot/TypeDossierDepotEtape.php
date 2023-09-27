<?php

class TypeDossierDepotEtape implements TypeDossierEtapeSetSpecificInformation
{
    public function setSpecificInformation(
        TypeDossierEtapeProperties $typeDossierEtape,
        array $result,
        StringMapper $stringMapper
    ): array {
        $error_ged_state = $stringMapper->get('error-ged');
        $result[DocumentType::ACTION]['supression'][Action::ACTION_RULE]
        [Action::ACTION_RULE_LAST_ACTION][] = $error_ged_state;
        return $result;
    }
}
