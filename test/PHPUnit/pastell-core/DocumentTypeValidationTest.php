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

    public function dataProvider(): array
    {
        $filePath = __DIR__ . '/fixtures/definition-with-wrong-';
        return [
            [
                $filePath . 'actionClass.yml',
                ["action:test:action-class:<b>Supprime</b> n'est pas disponible sur le système"]
            ],
            [
                $filePath . 'connecteurType.yml',
                [
                    "action:<b>test</b>:connecteur-type:<b>signatures</b> n'est pas un connecteur du système",
                    'action:<b>test</b>:connecteur-type-action:<b>SignatureEnvoies</b> '
                    . "n'est pas une classe d'action du système",
                    "action:<b>test</b>:connecteur-type-mapping:document:<b>toto</b> n'est pas un élément du formulaire"
                ]
            ],
            [
                $filePath . 'actionProperties.yml',
                ["formulaire:xx:<b>toto</b> n'est pas une clé de <b>action</b>"]
            ],
            [
                $filePath . 'actionSelection.yml',
                ["action:test:action-selection:<b>toto</b> n'est pas un type d'entité du système"]
            ],
            [
                $filePath . 'champs.yml',
                [
                    "champs-recherche-avancee:<b>toto</b> n'est pas une valeur par défaut "
                    . 'ou un élément indexé du formulaire',
                    "champs-affiches:<b>toto</b> n'est pas une valeur par défaut ou un élément indexé du formulaire"
                ]
            ],
            [
                $filePath . 'connecteur.yml',
                ["connecteur:<b>signatures</b> n'est défini dans aucun connecteur du système"]
            ],
            [
                $filePath . 'depend.yml',
                ["<b>formulaire:Message:test:depend:test2</b> n'est pas un élément du formulaire"]
            ],
            [
                $filePath . 'editableContent.yml',
                ["formulaire:xx:yy:editable-content:<b>signature</b> n'est pas défini dans le formulaire"]
            ],
            [
                $filePath . 'formulaireProperties.yml',
                [
                    "formulaire:choice-action:<b>toto</b> n'est pas une clé de <b>action</b>",
                    "formulaire:onchange:<b>toto</b> n'est pas une clé de <b>action</b>",
                ]
            ],
            [
                $filePath . 'isEqual.yml',
                ["formulaire:xx:yy:is_equal:<b>test3</b> n'est pas défini dans le formulaire"]
            ],
            [
                $filePath . 'one-title.yml',
                ['Plusieurs éléments trouvés avec la propriété « <b>title</b> » : test,test2']
            ],
            [
                $filePath . 'pageCondition.yml',
                [
                    "page-condition:<b>Parapheur</b> n'est pas une clé de <b>formulaire</b>",
                    "page-condition:<b>Bordereau:envoi</b> n'est pas un élément du <b>formulaire</b>"
                ]
            ],
            [
                $filePath . 'readOnlyContent.yml',
                ["formulaire:xx:yy:read-only-content:<b>toto</b> n'est pas défini dans le formulaire"]
            ],
            [
                $filePath . 'ruleAction.yml',
                [
                    "formulaire:last-action:<b>creation</b> n'est pas une clé de <b>action</b>",
                    "formulaire:has-action:<b>modification</b> n'est pas une clé de <b>action</b>",
                    "formulaire:no-action:<b>termine</b> n'est pas une clé de <b>action</b>"
                ]
            ],
            [
                $filePath . 'ruleContent.yml',
                ["action:xx:rule:content:<b>envoi_sae</b> n'est pas défini dans le formulaire"]
            ],
            [
                $filePath . 'ruleElement.yml',
                ["<b>supression:rule</b>: la clé <b>toto</b> n'est pas attendu"]
            ],
            [
                $filePath . 'ruleTypeIdE.yml',
                ["action:*:rule:type_id_e:<b>collectivites</b> n'est pas un type d'entité du système"]
            ],
            [
                $filePath . 'valueWithType.yml',
                [
                    'La propriété <b>value</b> pour <b>Acte:acte_nature</b> '
                        . 'est réservé pour les éléments de type <b>select</b>'
                ]
            ],
        ];
    }

    /**
     * @dataProvider dataProvider
     */
    public function testValidationFailed(string $filePath, array $expectedError): void
    {
        static::assertFalse($this->documentTypeValidation->isDefinitionFileValid($filePath));
        static::assertEquals($expectedError, $this->documentTypeValidation->getErrorList($filePath));
    }
}
