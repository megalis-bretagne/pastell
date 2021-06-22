<?php

abstract class TransformationConnecteur extends Connecteur
{
    abstract public function transform(DonneesFormulaire $donneesFormulaire): array;
}
