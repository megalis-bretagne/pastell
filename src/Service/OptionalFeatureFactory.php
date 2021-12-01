<?php

namespace Pastell\Service;

use ObjectInstancier;
use Pastell\Helpers\ClassHelper;

class OptionalFeatureFactory
{
    private $objectInstancier;

    public function __construct(ObjectInstancier $objectInstancier)
    {
        $this->objectInstancier = $objectInstancier;
    }

    /**
     * @param string $classname
     * @return IOptionalFeature
     */
    public function getFeature(string $classname): IOptionalFeature
    {
        /** @var IOptionalFeature $featureInstance */
        return $this->objectInstancier->getInstance($classname);
    }

    public function isEnabled(string $classname): bool
    {
        if (! class_exists($classname)) {
            return false;
        }
        return $this->getFeature($classname)->isEnable();
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
        $featuresList = ClassHelper::findRecursive("Pastell\Service\OptionalFeatures");
        foreach ($featuresList as $classname) {
            /** @var IOptionalFeature $feature */
            $feature = $this->objectInstancier->getInstance($classname);
            $result[$classname] = [
                'description' => $feature->getDescription(),
                'is_enable' => $feature->isEnable(),
                'is_enable_by_default' => $feature->isEnableByDefault(),
            ];
        }
        return $result;
    }
}
