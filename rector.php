<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Php74\Rector\Property\TypedPropertyRector;
use Rector\Set\ValueObject\LevelSetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // get parameters
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PATHS, [
        __DIR__ . '/action',
        __DIR__ . '/api',
        __DIR__ . '/connecteur',
        __DIR__ . '/connecteur-type',
        __DIR__ . '/controler',
        __DIR__ . '/installation',
        __DIR__ . '/lib',
        __DIR__ . '/mail',
        __DIR__ . '/model',
        __DIR__ . '/module',
        __DIR__ . '/pastell-core',
        __DIR__ . '/script',
        __DIR__ . '/src',
        __DIR__ . '/template',
        __DIR__ . '/test',
        __DIR__ . '/tests',
        __DIR__ . '/type-dossier',
        __DIR__ . '/visionneuse',
        __DIR__ . '/web',
        __DIR__ . '/web-mailsec',
    ]);

    // Define what rule sets will be applied
    $containerConfigurator->import(LevelSetList::UP_TO_PHP_81);

    // get services (needed for register a single rule)
    // $services = $containerConfigurator->services();

    // register a single rule
    // $services->set(TypedPropertyRector::class);
};
