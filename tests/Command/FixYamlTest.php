<?php

declare(strict_types=1);

namespace Pastell\Tests\Command;

use Pastell\Command\FixYaml;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class FixYamlTest extends TestCase
{
    public function testFix(): void
    {
         copy(__DIR__ . '/fixtures/definition-spyc.yml', '/tmp/actes-generiques.yml');
         $command = new FixYaml();
         $commandTester = new CommandTester($command);
         $commandTester->execute(['file' => '/tmp/actes-generiques.yml']);
         self::assertFileEquals(
             __DIR__ . '/fixtures/definition-symfony.yml',
             '/tmp/actes-generiques.yml'
         );
    }

    public function testFixIsIdempotent(): void
    {
        copy(__DIR__ . '/fixtures/definition-symfony.yml', '/tmp/definition-symfony.yml');
        $command = new FixYaml();
        $commandTester = new CommandTester($command);
        $commandTester->execute(['file' => '/tmp/definition-symfony.yml']);
        self::assertFileEquals(
            __DIR__ . '/fixtures/definition-symfony.yml',
            '/tmp/definition-symfony.yml'
        );
    }
}
