<?php

declare(strict_types=1);

namespace Pastell\Bootstrap;

class Updater implements InstallableBootstrap
{
    public function __construct(
        private readonly \Pastell\Updater $updater,
    ) {
    }

    public function install(): InstallResult
    {
        $this->updater->update();
        return InstallResult::InstallOk;
    }

    public function getName(): string
    {
        return 'Mise à jour de version';
    }
}
