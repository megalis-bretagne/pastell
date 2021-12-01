<?php

namespace Pastell\Service;

abstract class OptionalFeatureDefaultImplementation implements IOptionalFeature
{
    protected $isEnable = false;

    public function __construct()
    {
        $this->isEnable = $this->isEnableByDefault();
    }

    public function isEnableByDefault(): bool
    {
        return false;
    }

    public function enable(): void
    {
        $this->isEnable = true;
    }

    public function disable(): void
    {
        $this->isEnable = false;
    }

    public function isEnable(): bool
    {
        return $this->isEnable;
    }
}
