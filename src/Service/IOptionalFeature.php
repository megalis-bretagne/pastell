<?php

namespace Pastell\Service;

interface IOptionalFeature
{
    public function isEnableByDefault(): bool;
    public function enable(): void;
    public function disable(): void;
    public function isEnable(): bool;
    public function getDescription(): string;
}
