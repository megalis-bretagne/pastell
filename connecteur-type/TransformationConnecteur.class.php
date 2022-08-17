<?php

abstract class TransformationConnecteur extends Connecteur
{
    public const ELEMENT_ID_MAX_LENGTH = 64;
    public const ELEMENT_ID_REGEXP = "^[0-9a-z_]+$";

    abstract public function transform(DonneesFormulaire $donneesFormulaire): array;
}
