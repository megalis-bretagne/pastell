<?php

namespace Pastell\Service\OptionalFeatures;

use Pastell\Service\OptionalFeatureDefaultImplementation;

class DisplayOptionalFeatureInTestPage extends OptionalFeatureDefaultImplementation
{
    public function isEnableByDefault(): bool
    {
        return true;
    }

    public function getDescription(): string
    {
        return "Affichage des fonctions optionnelles dans les tests du systèmes";
    }
}
