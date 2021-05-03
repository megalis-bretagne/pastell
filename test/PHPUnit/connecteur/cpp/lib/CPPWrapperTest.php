<?php

use Monolog\Logger;

require_once __DIR__ . "/../../../../../connecteur/cpp/lib/CPPWrapper.class.php";

class CPPWrapperTest extends ExtensionCppTestCase
{

    private const MEMORY_KEY = "pastell_token_piste_61cde1ef-41ab-441c-b23f-95991f9d919g";
    private const TOKEN = "Bearer BHv3LJUSWnGl5JRzxm8948mqhvv8P1UQLtCdjj1HgKdm8vQgmkeWQF";

    /** @var CPPWrapper */
    private $cppWrapper;

    /** @var CPPWrapperConfig */
    private $cppWrapperConfig;

    public function setUp()
    {
        parent::setUp();
        $this->getObjectInstancier()->getInstance(MemoryCache::class)->store(self::MEMORY_KEY, self::TOKEN);
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->getObjectInstancier()->getInstance(MemoryCache::class)->delete(self::MEMORY_KEY);
    }

    /**
     * @return CPPWrapperConfig
     */
    private function getDefaultWrapperConfig(): CPPWrapperConfig
    {
        $cppWrapperConfig = new CPPWrapperConfig();

        $cppWrapperConfig->user_login = "TEST";
        $cppWrapperConfig->user_password = "TEST";

        $cppWrapperConfig->url_piste_get_token = "https://sandbox-oauth.aife.economie.gouv.fr/api/oauth/token";
        $cppWrapperConfig->client_id = "61cde1ef-41ab-441c-b23f-95991f9d919g";
        $cppWrapperConfig->client_secret = "bd307b18-298e-45a7-a4ef-9169200fad63";
        $cppWrapperConfig->url_piste_api = "https://sandbox-api.aife.economie.gouv.fr/";
        $cppWrapperConfig->cpro_account = base64_encode($cppWrapperConfig->user_login . ":" . $cppWrapperConfig->user_password);

        return $cppWrapperConfig;
    }

    /**
     * @param CPPWrapperConfig|null $cppWrapperConfig
     * @return CPPWrapper
     * @throws CPPException
     */
    private function getCPPWrapper(CPPWrapperConfig $cppWrapperConfig = null): CPPWrapper
    {
        $cppWrapper = new CPPWrapper(
            $this->getObjectInstancier()->getInstance(CurlWrapperFactory::class),
            $this->getObjectInstancier()->getInstance(MemoryCache::class),
            $this->getObjectInstancier()->getInstance(UTF8Encoder::class),
            $this->getObjectInstancier()->getInstance(Logger::class)
        );
        $cppWrapper->setCppWrapperConfig($cppWrapperConfig ?? $this->getDefaultWrapperConfig());
        return $cppWrapper;
    }

    /**
     * @return array
     */
    public function getIsRaccordementCertificatProvider()
    {
        return [
            'RaccordementCertificat' => [true],
            'NotRaccordementCertificat' => [false],
        ];
    }

    /**
     * @param $is_raccordement_certificat
     * @throws Exception
     * @dataProvider getIsRaccordementCertificatProvider
     */
    public function testTestConnexion($is_raccordement_certificat)
    {
        $returnData = [
            'codeRetour' => 0,
            'libelle' => 'libelle',
            'listeFactures' => [
                [
                    'idFacture' => 1234
                ]
            ]
        ];
        $curlWrapper = $this->getMockBuilder(CurlWrapper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $curlWrapper->expects($this->any())->method('get')->willReturn(json_encode($returnData));
        $curlWrapper->expects($this->any())->method('getLastHttpCode')->willReturn(200);

        $curlWrapperFactory = $this->getMockBuilder(CurlWrapperFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $curlWrapperFactory->expects($this->any())->method('getInstance')->willReturn($curlWrapper);

        $this->getObjectInstancier()->setInstance(CurlWrapperFactory::class, $curlWrapperFactory);

        $this->cppWrapperConfig = $this->getDefaultWrapperConfig();
        $this->cppWrapperConfig->is_raccordement_certificat = $is_raccordement_certificat;
        $this->cppWrapper = $this->getCPPWrapper($this->cppWrapperConfig);

        $this->assertTrue($this->cppWrapper->testConnexion());
    }

    /**
     * When successfully getting the cpp id of the invoice
     * @test
     * @throws Exception
     * @param $is_raccordement_certificat
     * @dataProvider getIsRaccordementCertificatProvider
     */
    public function whenGettingTheInvoiceCppId($is_raccordement_certificat)
    {
        $returnData = [
            'codeRetour' => 0,
            'libelle' => 'libelle',
            'listeFactures' => [
                [
                    'idFacture' => 1234
                ]
            ]
        ];
        $curlWrapper = $this->getMockBuilder(CurlWrapper::class)->disableOriginalConstructor()->getMock();
        $curlWrapper->expects($this->any())->method('get')->willReturn(json_encode($returnData));
        $curlWrapper->expects($this->any())->method('getLastHttpCode')->willReturn("200");

        $curlWrapperFactory = $this->getMockBuilder(CurlWrapperFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $curlWrapperFactory->expects($this->any())->method('getInstance')->willReturn($curlWrapper);

        $this->getObjectInstancier()->setInstance(CurlWrapperFactory::class, $curlWrapperFactory);

        $this->cppWrapperConfig = $this->getDefaultWrapperConfig();
        $this->cppWrapperConfig->is_raccordement_certificat = $is_raccordement_certificat;
        $this->cppWrapper = $this->getCPPWrapper($this->cppWrapperConfig);

        $this->assertEquals(1234, $this->cppWrapper->getCppInvoiceId(1, '1111'));
    }

    /**
     * When no invoice is returned by chorus
     * @test
     * @throws Exception
     * @param $is_raccordement_certificat
     * @dataProvider getIsRaccordementCertificatProvider
     */
    public function whenNoInvoiceIsReturned($is_raccordement_certificat)
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Impossible de trouver la facture 1111");
        $returnData = [
            'codeRetour' => 0,
            'libelle' => 'libelle',
            'listeFactures' => [
            ]
        ];
        $curlWrapper = $this->getMockBuilder(CurlWrapper::class)->disableOriginalConstructor()->getMock();
        $curlWrapper->expects($this->any())->method('get')->willReturn(json_encode($returnData));
        $curlWrapper->expects($this->any())->method('getLastHttpCode')->willReturn("200");

        $curlWrapperFactory = $this->getMockBuilder(CurlWrapperFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $curlWrapperFactory->expects($this->any())->method('getInstance')->willReturn($curlWrapper);

        $this->getObjectInstancier()->setInstance(CurlWrapperFactory::class, $curlWrapperFactory);

        $this->cppWrapperConfig = $this->getDefaultWrapperConfig();
        $this->cppWrapperConfig->is_raccordement_certificat = $is_raccordement_certificat;
        $this->cppWrapper = $this->getCPPWrapper($this->cppWrapperConfig);

        $this->cppWrapper->getCppInvoiceId(1, '1111');
    }

    /**
     * When multiple invoices are returned by chorus (unlikely to happen)
     * @test
     * @throws Exception
     * @param $is_raccordement_certificat
     * @dataProvider getIsRaccordementCertificatProvider
     */
    public function whenMultipleInvoicesAreReturned($is_raccordement_certificat)
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Plusieurs factures ont été trouvé avec le numéro 1111");
        $returnData = [
            'codeRetour' => 0,
            'libelle' => 'libelle',
            'listeFactures' => [
                [
                    'idFacture' => 1234
                ],
                [
                    'idFacture' => 12345
                ]
            ]
        ];
        $curlWrapper = $this->getMockBuilder(CurlWrapper::class)->disableOriginalConstructor()->getMock();
        $curlWrapper->expects($this->any())->method('get')->willReturn(json_encode($returnData));
        $curlWrapper->expects($this->any())->method('getLastHttpCode')->willReturn("200");

        $curlWrapperFactory = $this->getMockBuilder(CurlWrapperFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $curlWrapperFactory->expects($this->any())->method('getInstance')->willReturn($curlWrapper);

        $this->getObjectInstancier()->setInstance(CurlWrapperFactory::class, $curlWrapperFactory);

        $this->cppWrapperConfig = $this->getDefaultWrapperConfig();
        $this->cppWrapperConfig->is_raccordement_certificat = $is_raccordement_certificat;
        $this->cppWrapper = $this->getCPPWrapper($this->cppWrapperConfig);

        $this->cppWrapper->getCppInvoiceId(1, '1111');
    }

    /**
     * @param $is_raccordement_certificat
     * @throws Exception
     * @dataProvider getIsRaccordementCertificatProvider
     */
    public function testGetIdentifiantStructureCPP($is_raccordement_certificat)
    {
        $returnData = [
            'codeRetour' => 0,
            'libelle' => 'libelle',
            'listeStructures' => [
                [
                    'idStructureCPP' => 25783752,
                    'identifiantStructure' => '00000000012887',
                    'designationStructure' => 'TAA070DESTINATAIRE',
                    'statut' => 'ACTIVE'
                ]
            ]
        ];
        $curlWrapper = $this->getMockBuilder(CurlWrapper::class)->disableOriginalConstructor()->getMock();
        $curlWrapper->expects($this->any())->method('get')->willReturn(json_encode($returnData));
        $curlWrapper->expects($this->any())->method('getLastHttpCode')->willReturn("200");

        $curlWrapperFactory = $this->getMockBuilder(CurlWrapperFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $curlWrapperFactory->expects($this->any())->method('getInstance')->willReturn($curlWrapper);

        $this->getObjectInstancier()->setInstance(CurlWrapperFactory::class, $curlWrapperFactory);

        $this->cppWrapperConfig = $this->getDefaultWrapperConfig();
        $this->cppWrapperConfig->is_raccordement_certificat = $is_raccordement_certificat;
        $this->cppWrapper = $this->getCPPWrapper($this->cppWrapperConfig);

        $this->assertEquals(25783752, $this->cppWrapper->GetIdentifiantStructureCPPByIdentifiantStructure("00000000012887"));
    }

    /**
     * @param $is_raccordement_certificat
     * @throws Exception
     * @dataProvider getIsRaccordementCertificatProvider
     */
    public function testGetIdentifiantStructureCPPWhenFalse($is_raccordement_certificat)
    {

        $curlWrapper = $this->getMockBuilder(CurlWrapper::class)->disableOriginalConstructor()->getMock();
        $curlWrapper->expects($this->any())->method('get')->willReturn(false);
        $curlWrapper->expects($this->any())->method('getLastHttpCode')->willReturn("200");

        $curlWrapperFactory = $this->getMockBuilder(CurlWrapperFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $curlWrapperFactory->expects($this->any())->method('getInstance')->willReturn($curlWrapper);

        $this->getObjectInstancier()->setInstance(CurlWrapperFactory::class, $curlWrapperFactory);

        $this->cppWrapperConfig = $this->getDefaultWrapperConfig();
        $this->cppWrapperConfig->is_raccordement_certificat = $is_raccordement_certificat;
        $this->cppWrapper = $this->getCPPWrapper($this->cppWrapperConfig);

        $this->assertFalse($this->cppWrapper->GetIdentifiantStructureCPPByIdentifiantStructure(""));
    }

    /**
     * @throws Exception
     */
    public function testGetToken()
    {
        $this->getObjectInstancier()->getInstance(MemoryCache::class)->delete(self::MEMORY_KEY);
        $returnData = [
            'access_token' => '5TqQc6hAsUsmxD5UpSxmV0kXTgUJY7vNX6HWUodz3lfiwmWvERTjVp',
            'token_type' => 'Bearer',
            'expires_in' => 3600,
            'scope' => 'openid',
        ];
        $curlWrapperToken = $this->getMockBuilder(CurlWrapper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $curlWrapperToken->expects($this->any())->method('get')->willReturn(json_encode($returnData));
        $curlWrapperToken->expects($this->any())->method('getLastHttpCode')->willReturn(200);

        $curlWrapperFactoryToken = $this->getMockBuilder(CurlWrapperFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $curlWrapperFactoryToken->expects($this->any())->method('getInstance')->willReturn($curlWrapperToken);
        $this->getObjectInstancier()->setInstance(CurlWrapperFactory::class, $curlWrapperFactoryToken);

        $this->cppWrapper = $this->getCPPWrapper();
        $this->assertEquals(1, $this->cppWrapper->testConnexion());

        $token = $this->getObjectInstancier()->getInstance(MemoryCache::class)->fetch(self::MEMORY_KEY);
        $this->assertEquals("Bearer 5TqQc6hAsUsmxD5UpSxmV0kXTgUJY7vNX6HWUodz3lfiwmWvERTjVp", $token);
    }

    /**
     * @return array
     */
    public function getRechercheFactureTravauxProvider()
    {
        return [
            'FactureNotEmpty' =>
                [
                    false,
                    "MOA",
                    ["listeFactures" => [['idFactureTravaux' => 1234]]],
                ],
            'FactureEmpty_NotPiste' =>
                [
                    true,
                    "MOA",
                    ["listeFactures" => []],
                ],
            'FactureEmpty_NoRole' =>
                [
                    false,
                    "",
                    ["listeFactures" => []],
                ],
        ];
    }

    /**
     * @param $is_raccordement_certificat
     * @param $user_role
     * @param $result_expected
     * @throws CPPException
     * @throws Exception
     * @dataProvider getRechercheFactureTravauxProvider
     */
    public function testRechercheFactureTravaux($is_raccordement_certificat, $user_role, $result_expected)
    {
        $returnData = [
            'codeRetour' => 0,
            'libelle' => 'TRA_MSG_00.000',
            'parametresRetour' => [
                'pageCourante' => 1,
                'pages' => 1,
            ],
            'listeFacturesTravaux' => [
                [
                    'idFactureTravaux' => 1234
                ]
            ]
        ];

        $curlWrapper = $this->getMockBuilder(CurlWrapper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $curlWrapper->expects($this->any())->method('get')->willReturn(json_encode($returnData));
        $curlWrapper->expects($this->any())->method('getLastHttpCode')->willReturn(200);

        $curlWrapperFactory = $this->getMockBuilder(CurlWrapperFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $curlWrapperFactory->expects($this->any())->method('getInstance')->willReturn($curlWrapper);

        $this->getObjectInstancier()->setInstance(CurlWrapperFactory::class, $curlWrapperFactory);

        $this->cppWrapperConfig = $this->getDefaultWrapperConfig();
        $this->cppWrapperConfig->is_raccordement_certificat = $is_raccordement_certificat;
        $this->cppWrapperConfig->user_role = $user_role;
        $this->cppWrapper = $this->getCPPWrapper($this->cppWrapperConfig);

        $this->assertEquals($result_expected, $this->cppWrapper->rechercheFactureTravaux());
    }
}