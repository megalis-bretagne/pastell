<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Utils\Rector\UseSetViewParametersInsteadMagicMethod;

return static function (ContainerConfigurator $containerConfigurator): void {
    // get parameters
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PATHS, [
        __DIR__ . '/controler'
    ]);

    // Define what rule sets will be applied
    //$containerConfigurator->import(LevelSetList::UP_TO_PHP_81);

    // get services (needed for register a single rule)
    $services = $containerConfigurator->services();
    $services->set(UseSetViewParametersInsteadMagicMethod::class);

    // register a single rule
    // $services->set(TypedPropertyRector::class);
};
