<?php

namespace Pastell;

use ObjectInstancier;
use Pastell\Helpers\ClassHelper;
use PastellLogger;

class Updater
{
    public function __construct(
        private readonly PastellLogger $pastellLogger,
        private readonly ObjectInstancier $objectInstancier
    ) {
    }

    public function update(): void
    {
        $all_command = ClassHelper::findRecursive("Pastell\Updater");

        $this->pastellLogger->debug('Migration class : ' . implode(", ", $all_command));
        foreach ($all_command as $updaterClassName) {
            /** @var Updater $updater */
            $updater = $this->objectInstancier->getInstance($updaterClassName);
            $this->pastellLogger->debug("Start Migrate $updaterClassName");
            $this->pastellLogger->setName($updaterClassName);
            $updater->update();
            $this->pastellLogger->setName(self::class);
            $this->pastellLogger->debug("End Migrate $updaterClassName");
        }
    }
}
