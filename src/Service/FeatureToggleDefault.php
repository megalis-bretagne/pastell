<?php

namespace Pastell\Service;

abstract class FeatureToggleDefault implements FeatureToggle
{
    private $isEnabled;

    public function __construct()
    {
        $this->isEnabled = $this->isEnabledByDefault();
    }

    public function isEnabledByDefault(): bool
    {
        return false;
    }

    public function enable(): void
    {
        $this->isEnabled = true;
    }

    public function disable(): void
    {
        $this->isEnabled = false;
    }

    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }
}
