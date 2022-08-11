<?php

namespace Pastell\Service\FeatureToggle;

use Pastell\Service\FeatureToggleDefault;

class CDGFeature extends FeatureToggleDefault
{
    public function getDescription(): string
    {
        return "Permet d'associer une collectivité à un centre de gestion";
    }
}
