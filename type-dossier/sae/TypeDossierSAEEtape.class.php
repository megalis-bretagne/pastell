<?php

class TypeDossierSAEEtape implements TypeDossierEtapeSetSpecificInformation {

    public function setSpecificInformation(TypeDossierEtape $typeDossierEtape, array $result): array {
        if (empty($typeDossierEtape->specific_type_info['sae-has-metadata-in-json'])){
            unset($result['formulaire']['Configuration SAE']);
            unset($result['page-condition']['Configuration SAE']);
        }
        return $result;
    }
}