<?php

namespace Pastell\Tests\Command;

use ConnecteurEntiteSQL;
use ConnecteurFactory;
use DocumentEntite;
use DocumentSQL;
use DonneesFormulaireFactory;
use Exception;
use InvalidArgumentException;
use NotFoundException;
use Pastell\Command\UpdateField;
use Pastell\Service\SimpleTwigRenderer;
use PastellTestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class UpdateFieldTest extends PastellTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $command = new UpdateField(
            $this->getObjectInstancier()->getInstance(DocumentSQL::class),
            $this->getObjectInstancier()->getInstance(DonneesFormulaireFactory::class),
            $this->getObjectInstancier()->getInstance(DocumentEntite::class),
            $this->getObjectInstancier()->getInstance(ConnecteurEntiteSQL::class),
            $this->getObjectInstancier()->getInstance(ConnecteurFactory::class),
            $this->getObjectInstancier()->getInstance(SimpleTwigRenderer::class)
        );
        $this->commandTester = new CommandTester($command);
    }

    private function executeCommand(
        string $scope,
        string $type,
        string $field,
        string $twigExpression,
        string $confirm
    ): int {
        $this->commandTester->setInputs([$confirm]);
        return $this->commandTester->execute(
            [
                'scope' => $scope,
                'type' => $type,
                'field' => $field,
                'twigExpression' => $twigExpression,
            ]
        );
    }

    public function commandArgumentsProvider(): iterable
    {
        yield [
            'module',
            'test',
            'nom',
            '{% if nom == "" %}NouveauNom{% else %}{{nom}}{% endif %}',
            'yes',
            'UnNom',
            1
        ];
        yield [
            'module',
            'test',
            'prenom',
            '{% if prenom == "" %}NouveauPrenom{% else %}{{prenom}}{% endif %}',
            'yes',
            'NouveauPrenom',
            1
        ];
        yield [
            'connector',
            'test',
            'champs1',
            '{% if champs2 == "" %}NouvelleValeur{% else %}{{champs2}}{% endif %}',
            'yes',
            'NouvelleValeur',
            3
        ];
    }

    /**
     * @dataProvider commandArgumentsProvider
     * @throws NotFoundException
     * @throws Exception
     */
    public function testCommand(
        string $scope,
        string $type,
        string $field,
        string $twigExpression,
        string $confirm,
        string $newValue,
        int $documentsNumber
    ): void {

        if ($scope === UpdateField::SCOPE_MODULE) {
            $document = $this->createDocument('test');
            $this->configureDocument($document['id_d'], ['nom' => 'UnNom']);
        } else {
            $document = $this->createConnector('test', 'Connecteur Test entité');
        }

        $this->assertSame(
            0,
            $this->executeCommand($scope, $type, $field, $twigExpression, $confirm)
        );

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('0/' . $documentsNumber, $output);
        $this->assertStringContainsString($documentsNumber . '/' . $documentsNumber, $output);

        if ($scope === UpdateField::SCOPE_MODULE) {
            $donneesFormulaire = $this->getObjectInstancier()
                ->getInstance(DonneesFormulaireFactory::class)
                ->get($document['id_d']);
        } else {
            $donneesFormulaire = $this->getObjectInstancier()
                ->getInstance(ConnecteurFactory::class)
                ->getConnecteurConfig($document['id_ce']);
        }
        $this->assertSame(
            $newValue,
            $donneesFormulaire->get($field)
        );
    }

    public function testCommandUnknownScope(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid scope');
        $this->executeCommand('unknown', 'test', 'test', 'test', 'yes');
    }

    public function testCommandNoConfirmation(): void
    {
        $this->assertSame(
            1,
            $this->executeCommand('connector', 'test', 'test', 'test', 'no')
        );
    }
}
