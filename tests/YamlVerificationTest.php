<?php

declare(strict_types=1);

namespace Pastell\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class YamlVerificationTest extends TestCase
{
    public function testYaml(): void
    {
        $finder = new Finder();
        $finder
            ->in([
            __DIR__ . '/../module',
            __DIR__ . '/../connecteur/',
            __DIR__ . '/../type-dossier/',
        ])
            ->exclude('docker')
            ->name('*.yml');
        foreach ($finder as $file) {
            try {
                Yaml::parseFile($file->getPathname());
            } catch (ParseException $exception) {
                self::fail(
                    sprintf('Unable to parse the YAML file %s : %s', $file->getPathname(), $exception->getMessage())
                );
            }
        }
        $this->expectNotToPerformAssertions();
    }
}
