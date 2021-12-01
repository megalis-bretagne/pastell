<?php

namespace Pastell\Service;

use ObjectInstancier;
use Pastell\Helpers\ClassHelper;

class FeatureToggleService
{
    private $objectInstancier;

    public function __construct(ObjectInstancier $objectInstancier)
    {
        $this->objectInstancier = $objectInstancier;
    }

    /**
     * @param string $classname
     * @return FeatureToggle
     */
    public function getFeature(string $classname): FeatureToggle
    {
        return $this->objectInstancier->getInstance($classname);
    }

    public function isEnabled(string $classname): bool
    {
        if (! class_exists($classname)) {
            return false;
        }
        return $this->getFeature($classname)->isEnabled();
    }

    public function enable(string $classname): void
    {
        if (class_exists($classname)) {
            $this->getFeature($classname)->enable();
        }
    }

    public function disable(string $classname): void
    {
        if (class_exists($classname)) {
            $this->getFeature($classname)->disable();
        }
    }

    public function getAllOptionalFeatures(): array
    {
        $result = [];
        $featuresList = ClassHelper::findRecursive("Pastell\Service\FeatureToggle");
        foreach ($featuresList as $classname) {
            /** @var FeatureToggle $feature */
            $feature = $this->objectInstancier->getInstance($classname);
            $result[$classname] = [
                'description' => $feature->getDescription(),
                'is_enabled' => $feature->isEnabled(),
                'is_enabled_by_default' => $feature->isEnabledByDefault(),
            ];
        }
        return $result;
    }
}
