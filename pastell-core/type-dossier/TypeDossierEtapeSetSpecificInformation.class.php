<?php

interface TypeDossierEtapeSetSpecificInformation {

	public function setSpecificInformation(TypeDossierEtape $typeDossierEtape,array $result, StringMapper $stringMapper) : array;

}