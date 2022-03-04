<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\DeadCode\Rector\Cast\RecastingRemovalRector;
use Rector\Php74\Rector\Property\TypedPropertyRector;
use Rector\Set\ValueObject\LevelSetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // get parameters
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PATHS, [
        __DIR__ . '/action',
        __DIR__ . '/api',
        __DIR__ . '/batch',
        __DIR__ . '/bin',
        __DIR__ . '/ci-resources',
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
        __DIR__ . '/test/PHPUnit',
        __DIR__ . '/tests',
        __DIR__ . '/type-dossier',
        __DIR__ . '/visionneuse',
        __DIR__ . '/web',
        __DIR__ . '/web-mailsec',
    ]);
//    $parameters->set(Option::AUTO_IMPORT_NAMES, true);

    // Define what rule sets will be applied
    //$containerConfigurator->import(LevelSetList::UP_TO_PHP_81);
    $containerConfigurator->import(\Rector\PHPUnit\Set\PHPUnitLevelSetList::UP_TO_PHPUNIT_90);

    // get services (needed for register a single rule)
    $services = $containerConfigurator->services();

    // register a single rule
    //$services->set(TypedPropertyRector::class);
    //$services->set(RecastingRemovalRector::class);


};
