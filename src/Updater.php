<?php

namespace Pastell;

use ObjectInstancier;
use Pastell\Updater\Version;
use PastellLogger;

class Updater
{

    private static $classes = [
        '3.0.1' => Updater\Version301::class,
        '3.0.2' => Updater\Version302::class,
    ];

    /**
     * @var PastellLogger
     */
    private $pastellLogger;

    /**
     * @var ObjectInstancier
     */
    private $objectInstancier;

    public function __construct(PastellLogger $pastellLogger, ObjectInstancier $objectInstancier)
    {
        $this->pastellLogger = $pastellLogger;
        $this->objectInstancier = $objectInstancier;
    }

    public function update(): void
    {
        foreach (self::$classes as $version => $class) {
            $this->executeUpdate($version);
        }
    }

    /**
     * @throws UpdaterException
     */
    public function to(string $version): void
    {
        if (!isset(self::$classes[$version])) {
            throw new UpdaterException("The update to version \"$version\" does not exist");
        }
        $this->executeUpdate($version);
    }

    private function executeUpdate(string $version): void
    {
        /** @var Version $versionUpdater */
        $versionUpdater = $this->objectInstancier->getInstance(self::$classes[$version]);
        $this->pastellLogger->info("Start script to $version");
        $versionUpdater->update();
        $this->pastellLogger->info("End script to $version");
    }
}
