<?php

interface TypeDossierEtapeSetSpecificInformation
{
    public function setSpecificInformation(TypeDossierEtapeProperties $typeDossierEtape, array $result, StringMapper $stringMapper): array;
}
