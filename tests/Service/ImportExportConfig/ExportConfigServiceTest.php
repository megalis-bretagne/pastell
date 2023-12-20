<?php

declare(strict_types=1);

namespace Pastell\Tests\Service\ImportExportConfig;

use EntiteSQL;
use FakeTdT;
use FluxEntiteHeritageSQL;
use Pastell\Service\Entite\EntityCreationService;
use Pastell\Service\ImportExportConfig\ExportConfigService;
use Pastell\Service\ImportExportConfig\ImportConfigService;
use PastellTestCase;

class ExportConfigServiceTest extends PastellTestCase
{
    /**
     * @throws \Exception
     */
    public function testAll(): void
    {
        $fluxEntiteSQl = $this->getObjectInstancier()->getInstance(\FluxEntiteSQL::class);
        $fluxEntiteSQl->deleteConnecteur(1, 'fournisseur-invitation', 'mail-fournisseur-invitation');
        $id_ce = $this->createConnector('test', "foo", 2)['id_ce'];
        $this->configureConnector($id_ce, [
            'champs1' => 'bar',
        ], 2);
        $connectorConfig = $this->getConnecteurFactory()->getConnecteurConfig($id_ce);
        $connectorConfig->addFileFromData("champs6", "foo.txt", "barbaz");

        $this->associateFluxWithConnector($id_ce, "test", "test", 2);
        $this->associateFluxWithConnector(2, "actes-generique", "TdT", 2);

        $entityCreationService = $this->getObjectInstancier()->getInstance(EntityCreationService::class);
        $id_e_herite = $entityCreationService->create(
            'Entite qui hérite',
            '',
            EntiteSQL::TYPE_COLLECTIVITE,
            self::ID_E_COL
        );

        /** @var FluxEntiteHeritageSQL $fluxEntiteHeritageSQL */
        $fluxEntiteHeritageSQL = $this->getObjectInstancier()->getInstance(FluxEntiteHeritageSQL::class);
        $fluxEntiteHeritageSQL->setInheritanceAllFlux($id_e_herite);

        $id_e_herite_actes = $entityCreationService->create(
            'Entite qui hérite que de actes',
            '',
            EntiteSQL::TYPE_COLLECTIVITE,
            self::ID_E_COL
        );
        /** @var FluxEntiteHeritageSQL $fluxEntiteHeritageSQL */
        $fluxEntiteHeritageSQL = $this->getObjectInstancier()->getInstance(FluxEntiteHeritageSQL::class);
        $fluxEntiteHeritageSQL->setInheritance($id_e_herite_actes, 'actes_generique');

        /** @var ExportConfigService $exportConfigService */
        $exportConfigService = $this->getObjectInstancier()->getInstance(ExportConfigService::class);
        $exportedInfo = $exportConfigService->getInfo(1, [
            ExportConfigService::INCLUDE_CONNECTOR => true,
            ExportConfigService::INCLUDE_ENTITY => true,
            ExportConfigService::INCLUDE_CHILD => true,
            ExportConfigService::INCLUDE_ASSOCIATION => true,
            ]);
        $id_e_root = $entityCreationService->create(
            "Entité d'importation",
            '',
        );
        $importConfigService = $this->getObjectInstancier()->getInstance(ImportConfigService::class);

        $importConfigService->import($exportedInfo, $id_e_root);
        /** @var EntiteSQL $entiteSQL */
        $entiteSQL = $this->getObjectInstancier()->getInstance(EntiteSQL::class);

        $fille = $entiteSQL->getFille($id_e_root);
        self::assertEquals('Bourg-en-Bresse', $fille[0]['denomination']);
        $petiteFille = $entiteSQL->getFille($fille[0]['id_e']);
        self::assertEquals('CCAS', $petiteFille[0]['denomination']);

        /** @var \ConnecteurEntiteSQL $connecteurEntiteSQL */
        $connecteurEntiteSQL = $this->getObjectInstancier()->getInstance(\ConnecteurEntiteSQL::class);
        $connectorList = $connecteurEntiteSQL->getAll($petiteFille[0]['id_e']);
        self::assertEquals('foo', $connectorList[0]['libelle']);

        $connectorConfig = $this->getConnecteurFactory()->getConnecteurConfig($connectorList[0]['id_ce']);
        self::assertEquals('bar', $connectorConfig->get('champs1'));

        self::assertEquals('barbaz', $connectorConfig->getFileContent('champs6'));
        self::assertEquals('foo.txt', $connectorConfig->getFileName('champs6'));

        $connectorConfig = $this->getConnecteurFactory()
            ->getConnecteurConfigByType($petiteFille[0]['id_e'], "test", "test");
        self::assertEquals('bar', $connectorConfig->get('champs1'));

        $connector = $this->getConnecteurFactory()
            ->getConnecteurByType($petiteFille[0]['id_e'], "actes-generique", "Tdt");
        self::assertInstanceOf(FakeTdT::class, $connector);
        self::assertTrue($fluxEntiteHeritageSQL->hasInheritanceAllFlux($petiteFille[1]['id_e']));
        self::assertTrue($fluxEntiteHeritageSQL->hasInheritance($petiteFille[2]['id_e'], 'actes_generique'));
    }

    public function testWhenIdEntityNotFound(): void
    {
        $importConfigService = $this->getObjectInstancier()->getInstance(ImportConfigService::class);
        $importConfigService->import(
            [
                ExportConfigService::ENTITY_CHILD => [
                    [
                    'entite_mere' => 42,
                    'siren' => '',
                    'denomination' => 'Foo',
                    'type' => 'collectivite',
                    'id_e' => 12,
                    ]
                ]
            ],
            0
        );
        /** @var EntiteSQL $entiteSQL */
        $entiteSQL = $this->getObjectInstancier()->getInstance(EntiteSQL::class);
        $info = $entiteSQL->getInfoByDenomination('Foo');
        $this->assertEquals('Foo', $info['denomination']);
        $this->assertEquals(0, $info['entite_mere']);
        $this->assertEquals(
            [0 => "L'entité mère de Foo est inconnue, l'entité sera attachée à l'entité 0."],
            $importConfigService->getLastErrors()
        );
    }

    public function testWhenIdEntityNotFoundOnConnector(): void
    {
        /** @var \ConnecteurEntiteSQL $connecteurEntiteSQL */
        $connecteurEntiteSQL = $this->getObjectInstancier()->getInstance(\ConnecteurEntiteSQL::class);
        $numberOfConnectors = count($connecteurEntiteSQL->getAllLocal());
        $importConfigService = $this->getObjectInstancier()->getInstance(ImportConfigService::class);
        $importConfigService->import(
            [
                ExportConfigService::CONNECTOR_INFO => [
                    [
                        'id_e' => 12,
                        'libelle' => 'Bar',
                        'id_connecteur' => 'test',
                        'type' => 'test',
                        'id_ce' => 42,
                        'data' => json_encode(['metadata' => ['champs1' => 'Foo']]),
                    ]
                ]
            ],
            0
        );
        $this->assertCount($numberOfConnectors, $connecteurEntiteSQL->getAllLocal());
        $this->assertEquals(
            [0 => "Le connecteur Bar est attaché à une entité inconnue : il n'a pas été importé."],
            $importConfigService->getLastErrors()
        );
    }

    public function testWhenEntiteIdNotFoundOnAssociation(): void
    {
        $importConfigService = $this->getObjectInstancier()->getInstance(ImportConfigService::class);
        $importConfigService->import(
            [
                ExportConfigService::ASSOCIATION_INFO => [
                    12 => [
                        'actes-generique' => [
                            'Bordereau SEDA' => [
                                0 => [
                                    'num_same_type' => 0,
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            0
        );
        $this->assertEquals(
            [0 => "L'entité du fichier d'import id_e=12 n'est pas présente : ces associations n'ont pas été importées."],
            $importConfigService->getLastErrors()
        );
    }

    public function testWhenConnectorIdNotFoundOnAssociation(): void
    {
        $importConfigService = $this->getObjectInstancier()->getInstance(ImportConfigService::class);
        $importConfigService->import(
            [
                ExportConfigService::ENTITY_INFO => [
                    'denomination' => 'Foo',
                    'id_e' => 12,
                    'siren' => '000000000',
                    'entite_mere' => 0,
                    'type' => 'collectivite',
                ],
                ExportConfigService::ASSOCIATION_INFO => [
                    12 => [
                        'actes-generique' => [
                            'Bordereau SEDA' => [
                                0 => [
                                    'id_ce' => 42,
                                    'num_same_type' => 0,
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            0
        );
        $this->assertEquals(
            [0 => "La définition du connecteur id_ce=42 n'est pas présente : l'association n'a pas été importée."],
            $importConfigService->getLastErrors()
        );
    }

    public function testWhenEntityIdNotFoundOnConnectorInheritance(): void
    {
        $importConfigService = $this->getObjectInstancier()->getInstance(ImportConfigService::class);
        $importConfigService->import(
            [
                ExportConfigService::ASSOCIATION_HERITAGE_INFO => [
                    12 => ['actes-generique'],
                ],
            ],
            0
        );
        $this->assertEquals(
            [0 => "L'entité du fichier d'import id_e=12 n'est pas présente : les héritages d'associations n'ont pas été importées."],
            $importConfigService->getLastErrors()
        );
    }

    public function testWhenImportOnlyEntiteFille(): void
    {
        $importConfigService = $this->getObjectInstancier()->getInstance(ImportConfigService::class);
        $importConfigService->import(
            [
                ExportConfigService::ENTITY_CHILD => [
                    12 =>  [
                        'denomination' => 'Foo',
                        'id_e' => 12,
                        'siren' => '000000000',
                        'entite_mere' => 1,
                        'type' => 'collectivite',
                    ],
                ],
            ],
            1
        );

        $entiteSQL = $this->getObjectInstancier()->getInstance(EntiteSQL::class);
        self::assertEquals('Foo', $entiteSQL->getFille(1)[1]['denomination']);
    }

    /**
     * @throws \DonneesFormulaireException
     * @throws \JsonException
     */
    public function testWhenImportingGlobalConnectorOnRoot(): void
    {
        /** @var \ConnecteurEntiteSQL $connecteurEntiteSQL */
        $connecteurEntiteSQL = $this->getObjectInstancier()->getInstance(\ConnecteurEntiteSQL::class);
        $numberOfConnectors = count($connecteurEntiteSQL->getAllLocal());
        $importConfigService = $this->getObjectInstancier()->getInstance(ImportConfigService::class);
        $importConfigService->import(
            [
                ExportConfigService::CONNECTOR_INFO => [
                    [
                        'id_e' => 0,
                        'libelle' => 'Bar',
                        'id_connecteur' => 'test',
                        'type' => 'test',
                        'id_ce' => 42,
                        'data' => json_encode(['metadata' => ['champs1' => 'Foo']], JSON_THROW_ON_ERROR),
                    ]
                ]
            ],
            0
        );
        $this->assertCount($numberOfConnectors, $connecteurEntiteSQL->getAllLocal());
    }

    /**
     * @throws \DonneesFormulaireException
     * @throws \JsonException
     */
    public function testWhenImportingGlobalConnectorOnChild(): void
    {
        /** @var \ConnecteurEntiteSQL $connecteurEntiteSQL */
        $connecteurEntiteSQL = $this->getObjectInstancier()->getInstance(\ConnecteurEntiteSQL::class);
        $numberOfConnectors = count($connecteurEntiteSQL->getAllLocal());
        $importConfigService = $this->getObjectInstancier()->getInstance(ImportConfigService::class);
        $importConfigService->import(
            [
                ExportConfigService::CONNECTOR_INFO => [
                    [
                        'id_e' => 0,
                        'libelle' => 'Bar',
                        'id_connecteur' => 'test',
                        'type' => 'test',
                        'id_ce' => 42,
                        'data' => json_encode(['metadata' => ['champs1' => 'Foo']], JSON_THROW_ON_ERROR),
                    ]
                ]
            ],
            1
        );
        $this->assertCount($numberOfConnectors, $connecteurEntiteSQL->getAllLocal());
        $this->assertEquals(
            [0 => "Le connecteur global Bar ne peut pas être importé sur une entité fille : il n'a pas été importé."],
            $importConfigService->getLastErrors()
        );
    }

    public function testWhenImportOnlyEntiteConnector(): void
    {
        $importConfigService = $this->getObjectInstancier()->getInstance(ImportConfigService::class);
        $importConfigService->import(
            [
                ExportConfigService::CONNECTOR_INFO => [
                    [
                        'id_e' => 12,
                        'libelle' => 'Bar',
                        'id_connecteur' => 'test',
                        'type' => 'test',
                        'id_ce' => 42,
                        'data' => json_encode(['metadata' => ['champs1' => 'Foo']]),
                    ]
                ],
            ],
            1
        );
        $connectorConfig = $this->getConnecteurFactory()
            ->getConnecteurConfig(14);
        self::assertEquals('Foo', $connectorConfig->get('champs1'));
    }
}
