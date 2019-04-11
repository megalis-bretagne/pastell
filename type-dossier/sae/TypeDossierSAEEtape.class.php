<?php

class TypeDossierSAEEtape implements TypeDossierEtapeSetSpecificInformation {

    public function setSpecificInformation(TypeDossierEtapeProperties $typeDossierEtape, array $result, StringMapper $stringMapper): array {

    	$config_sae = $stringMapper->get('Configuration SAE');

        if (empty($typeDossierEtape->specific_type_info['sae-has-metadata-in-json'])){
            unset($result['formulaire'][$config_sae]);
            unset($result['page-condition'][$config_sae]);
        }
        return $result;
    }
}