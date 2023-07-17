<?php

use Pastell\Configuration\DocumentTypeValidation;

class DocumentTypeValidationTest extends PastellTestCase
{
    private DocumentTypeValidation $documentTypeValidation;

    protected function setUp(): void
    {
        $this->documentTypeValidation =
            $this->getObjectInstancier()->getInstance(DocumentTypeValidation::class);
    }

    public function testValidate()
    {
        static::assertTrue(
            $this->documentTypeValidation->isDefinitionFileValid(PASTELL_PATH . '/module/mailsec/definition.yml')
        );
    }

    public function testGetModuleDefinition()
    {
        static::assertNotEmpty($this->documentTypeValidation->getModuleDefinition());
    }

    public function testGetLastError()
    {
        static::assertFalse($this->documentTypeValidation->isDefinitionFileValid(''));
        static::assertEquals('File "" does not exist.', $this->documentTypeValidation->getErrorList('')[0]);
    }

    public function testConnecteurType(): void
    {
        static::assertTrue($this->documentTypeValidation->isDefinitionFileValid(
            __DIR__ . '/fixtures/definition-for-action-test.yml'
        ));
    }

    public function testConnecteurTypeAbsent(): void
    {
        $filePath = __DIR__ . '/fixtures/definition-with-connecteur-type.yml';
        static::assertFalse($this->documentTypeValidation->isDefinitionFileValid($filePath));
        static::assertEquals(
            "action:<b>test</b>:connecteur-type:<b>signatures</b> n'est pas un connecteur du système",
            $this->documentTypeValidation->getErrorList($filePath)[0]
        );
        static::assertEquals(
            'action:<b>test</b>:connecteur-type-action:<b>FakeSignatureEnvoie</b> '
            . "n'est pas une classe d'action du système",
            $this->documentTypeValidation->getErrorList($filePath)[1]
        );
    }

    public function testConnecteurTypeMappingFailed()
    {
        $filePath = __DIR__ . '/fixtures/definition-with-connecteur-type-failed.yml';
        static::assertFalse($this->documentTypeValidation->isDefinitionFileValid($filePath));
        static::assertEquals(
            "action:<b>test</b>:connecteur-type-mapping:document:<b>toto</b> n'est pas un élément du formulaire",
            $this->documentTypeValidation->getErrorList($filePath)[0]
        );
    }

    public function testModifiationNoChangeEtat()
    {
        static::assertTrue($this->documentTypeValidation->isDefinitionFileValid(
            __DIR__ . '/fixtures/definition-with-modification-no-change-etat.yml'
        ));
    }

    public function testRestrictionPack()
    {
        static::assertTrue($this->documentTypeValidation->isDefinitionFileValid(
            __DIR__ . '/fixtures/definition-with-restriction-pack.yml'
        ));
    }

    public function testRestrictionPackAbsent()
    {
        $filePath = __DIR__ . '/fixtures/definition-with-wrong_restriction-pack.yml';
        static::assertFalse($this->documentTypeValidation->isDefinitionFileValid($filePath));
        static::assertEquals(
            "restriction_pack:<b>pack_wrong_pack</b> n'est pas défini dans la liste des packs",
            $this->documentTypeValidation->getErrorList($filePath)[0]
        );
    }
}
