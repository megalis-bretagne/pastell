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
    public function testExecute(): void
    {
        $tmpDir = new TmpFolder();
        $tmp_dir = $tmpDir->create();

        $command = $this->getObjectInstancier()->getInstance(MakeModuleFromStudioDefinition::class);
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'source' => __DIR__ . '/fixtures/document-autorisation-urbanisme-draft.json',
            'target' => $tmp_dir,
            '--id' => 'document-autorisation-urbanisme',
            '--name' => "Document d'autorisation d'urbanisme",
            '--restriction_pack' => 'pack_urbanisme'
        ]);

        $output = $commandTester->getDisplay();

        static::assertStringContainsString('', $output);
        static::assertFileExists($tmp_dir . '/document-autorisation-urbanisme/definition.yml');

//        \file_put_contents(
//            __DIR__ . '/fixtures/expected-definition.yml',
//            \file_get_contents($tmp_dir . '/document-autorisation-urbanisme/definition.yml')
//        );
        static::assertFileEquals(
            __DIR__ . '/fixtures/expected-definition.yml',
            $tmp_dir . '/document-autorisation-urbanisme/definition.yml'
        );

        $ymlLoader = $this->getObjectInstancier()->getInstance(\YMLLoader::class);
        $def_array = $ymlLoader->getArray($tmp_dir . '/document-autorisation-urbanisme/definition.yml');
        $studio_def = base64_decode($def_array['studio_definition']);
        static::assertJson($studio_def);
        $json = json_decode($studio_def, true, 512, JSON_THROW_ON_ERROR);
        static::assertEquals('document-autorisation-urbanisme-draft', $json['raw_data']['id_type_dossier']);
        $tmpDir->delete($tmp_dir);
    }
}
