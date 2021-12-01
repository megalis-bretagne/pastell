<?php

namespace Pastell\Service;

interface FeatureToggle
{
    public function isEnabledByDefault(): bool;
    public function enable(): void;
    public function disable(): void;
    public function isEnabled(): bool;
    public function getDescription(): string;
}
