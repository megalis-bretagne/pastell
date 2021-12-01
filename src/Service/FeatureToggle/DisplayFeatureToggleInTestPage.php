<?php

namespace Pastell\Service\FeatureToggle;

use Pastell\Service\FeatureToggleDefault;

class DisplayFeatureToggleInTestPage extends FeatureToggleDefault
{
    public function isEnabledByDefault(): bool
    {
        return true;
    }

    public function getDescription(): string
    {
        return "Affichage des fonctions optionnelles dans le test du système";
    }
}
