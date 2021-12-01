<?php

namespace Pastell\Service\FeatureToggle;

use Pastell\Service\FeatureToggleDefault;

class TestingFeature extends FeatureToggleDefault
{
    public function getDescription(): string
    {
        return "Fonction permettant de tester les fonctionnalités optionnelles";
    }
}
