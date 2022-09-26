<?php

declare(strict_types=1);

namespace Pastell\Bootstrap;

interface InstallableBootstrap
{
    public function install(): InstallResult;
    public function getName(): string;
}
