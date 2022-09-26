<?php

declare(strict_types=1);

namespace Pastell\Bootstrap;

use ObjectInstancier;
use Pastell\Helpers\ClassHelper;
use PastellLogger;
use ReflectionClass;
use ReflectionException;

class Bootstrap
{
    public function __construct(
        public readonly ObjectInstancier $objectInstancier,
    ) {
    }

    /**
     * @throws ReflectionException
     */
    public function bootstrap(): void
    {
        $pastellLogger = $this->objectInstancier->getInstance(PastellLogger::class);
        $installableList = ClassHelper::findRecursive('Pastell\Bootstrap');
        foreach ($installableList as $installable) {
            $reflexionClass = new ReflectionClass($installable);
            if (! $reflexionClass->implementsInterface(InstallableBootstrap::class)) {
                continue;
            }
            /** @var InstallableBootstrap $installableObject */
            $installableObject = $this->objectInstancier->getInstance($installable);
            $pastellLogger->info('Installation : ' . $installableObject->getName());
            $result = $installableObject->install();
            if ($result === InstallResult::NothingToDo) {
                $pastellLogger->info('Déjà installé, aucune modification : ' . $installableObject->getName());
            }
            if ($result === InstallResult::InstallOk) {
                $pastellLogger->info('Installation OK : ' . $installableObject->getName());
            }
            if ($result === InstallResult::InstallFailed) {
                $pastellLogger->info('Installation en échec : ' . $installableObject->getName());
            }
        }
    }
}
