<?php

namespace Pastell\Service\OptionalFeatures;

use Pastell\Service\OptionalFeatureDefaultImplementation;

class TestingFeature extends OptionalFeatureDefaultImplementation
{
    public function getDescription(): string
    {
        return "Fonction permettant de tester les fonctionnalités optionnelles";
    }
}
