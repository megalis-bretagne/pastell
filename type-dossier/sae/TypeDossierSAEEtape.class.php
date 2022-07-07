<?php

class TypeDossierSAEEtape implements TypeDossierEtapeSetSpecificInformation
{
    use TypeDossierRemoveFromEditableContent;

    public function setSpecificInformation(TypeDossierEtapeProperties $typeDossierEtape, array $result, StringMapper $stringMapper): array
    {
        $config_sae = $stringMapper->get('Configuration SAE');
        $rejet_sae_action = $stringMapper->get('rejet-sae');

        if (empty($typeDossierEtape->specific_type_info['sae_has_metadata_in_json'])) {
            unset(
                $result[DocumentType::FORMULAIRE][$config_sae],
                $result[DocumentType::PAGE_CONDITION][$config_sae]
            );
            $this->removeFromEditableContent(['sae_config'], $result);
        }

        $result[DocumentType::ACTION]['supression'][Action::ACTION_RULE][Action::ACTION_RULE_LAST_ACTION][] = $rejet_sae_action;

        return $result;
    }
}
