<?php

class TypeDossierMailSecEtape implements TypeDossierEtapeSetSpecificInformation
{
    public function setSpecificInformation(
        TypeDossierEtapeProperties $typeDossierEtape,
        array $result,
        StringMapper $stringMapper
    ): array {
        $sendMailSecErrorAction = $stringMapper->get('send-mailsec-error');
        $result[DocumentType::ACTION][Action::MODIFICATION][Action::ACTION_RULE]
            [Action::ACTION_RULE_LAST_ACTION][] = $sendMailSecErrorAction;
        return $result;
    }
}
