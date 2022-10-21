<?php

declare(strict_types=1);

namespace Pastell\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class SymfonyContainerFactory extends WebTestCase
{
    public static function getSymfonyContainer(): ContainerInterface
    {
        return self::getContainer();
    }
}
