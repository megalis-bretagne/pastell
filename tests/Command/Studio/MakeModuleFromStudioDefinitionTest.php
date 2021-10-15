<?php

namespace Pastell\Tests\Command\Studio;

use Exception;
use Pastell\Command\Studio\MakeModuleFromStudioDefinition;
use PastellTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use TmpFolder;

class MakeModuleFromStudioDefinitionTest extends PastellTestCase
{
    /**
     * @throws Exception
     */
    public function testExecute()
    {
        $tmpDir = new TmpFolder();
        $tmp_dir = $tmpDir->create();

        $command = $this->getObjectInstancier()->getInstance(MakeModuleFromStudioDefinition::class);
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'source' => __DIR__ . "/fixtures/document-autorisation-urbanisme.json",
            'target' => $tmp_dir
        ]);

        $output = $commandTester->getDisplay();

        $this->assertStringContainsString('', $output);
        $this->assertFileExists($tmp_dir . "/document-autorisation-urbanisme/definition.yml");

        file_put_contents(__DIR__ . "/fixtures/expected-definition.yml", file_get_contents($tmp_dir . "/document-autorisation-urbanisme/definition.yml"));
        $this->assertFileEquals(
            __DIR__ . "/fixtures/expected-definition.yml",
            $tmp_dir . "/document-autorisation-urbanisme/definition.yml"
        );

        $tmpDir->delete($tmp_dir);
    }
}
