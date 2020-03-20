<?php

class PastellBootstrapTest extends PastellTestCase
{

    /** @var  PastellBootstrap */
    private $pastellBootstrap;

    protected function setUp()
    {
        parent::setUp();
        $this->pastellBootstrap = $this->getObjectInstancier()->getInstance('PastellBootstrap');
    }

    /**
     * @throws Exception
     */
    public function testTimestampCertificateAlreadyConfigured()
    {
        $this->pastellBootstrap->installHorodateur();
        $this->assertEquals("Le connecteur d'horodatage est configuré", $this->getLogRecords()[0]['message']);
    }

    /**
     * @throws Exception
     */
    public function testTimestampCertificate()
    {
        /** @var ConnecteurEntiteSQL $connecteurEntiteSQL */
        $connecteurEntiteSQL = $this->getObjectInstancier()->getInstance("ConnecteurEntiteSQL");
        $connecteurEntiteSQL->delete(10);
        $this->pastellBootstrap->installHorodateur();
        $this->assertEquals(
            "Horodateur interne installé et configuré avec un nouveau certificat autosigné",
            $this->getLogRecords()[3]['message']
        );
    }

    public function testInstallConnecteurFrequence()
    {

        $connecteurFrequenceSQL = $this->getObjectInstancier()->getInstance(ConnecteurFrequenceSQL::class);
        $connecteurFrequenceSQL->deleteAll();

        $this->pastellBootstrap->installConnecteurFrequenceDefault();
        $this->assertEquals(
            "Initialisation d'un connecteur avec une fréquence de 10 minutes pour les i-Parapheur",
            $this->getLogRecords()[1]['message']
        );

        $result = json_encode($connecteurFrequenceSQL->getAll());

        $connectors = [
            [
                'id_cf' => "3",
                "type_connecteur" => "",
                "famille_connecteur" => "",
                "id_connecteur" => "",
                "id_ce" => "",
                "action_type" => "",
                "type_document" => "",
                "action" => "",
                "expression" => "1",
                "id_verrou" => ""
            ],
            [
                "id_cf" => "5",
                "type_connecteur" => ConnecteurFrequence::TYPE_ENTITE,
                "famille_connecteur" => "Purge",
                "id_connecteur" => "purge",
                "id_ce" => "",
                "action_type" => "",
                "type_document" => "",
                "action" => "",
                "expression" => "1440",
                "id_verrou" => "PURGE"
            ],
            [
                "id_cf" => "6",
                "type_connecteur" => ConnecteurFrequence::TYPE_ENTITE,
                "famille_connecteur" => "SAE",
                "id_connecteur" => "",
                "id_ce" => "",
                "action_type" => "",
                "type_document" => "",
                "action" => "",
                "expression" => "10",
                "id_verrou" => ""
            ],
            [
                "id_cf" => "7",
                "type_connecteur" => ConnecteurFrequence::TYPE_ENTITE,
                "famille_connecteur" => "SAE",
                "id_connecteur" => "",
                "id_ce" => "",
                "action_type" => ConnecteurFrequence::TYPE_ACTION_DOCUMENT,
                "type_document" => "actes-generique",
                "action" => "verif-sae",
                "expression" => "60 X 24\n1440",
                "id_verrou" => ""
            ],
            [
                "id_cf" => "8",
                "type_connecteur" => ConnecteurFrequence::TYPE_ENTITE,
                "famille_connecteur" => "SAE",
                "id_connecteur" => "",
                "id_ce" => "",
                "action_type" => ConnecteurFrequence::TYPE_ACTION_DOCUMENT,
                "type_document" => "helios-generique",
                "action" => "verif-sae",
                "expression" => "60 X 24\n1440",
                "id_verrou" => ""
            ],
            [
                "id_cf" => "4",
                "type_connecteur" => ConnecteurFrequence::TYPE_ENTITE,
                "famille_connecteur" => "signature",
                "id_connecteur" => "iParapheur",
                "id_ce" => "",
                "action_type" => "",
                "type_document" => "",
                "action" => "",
                "expression" => "10",
                "id_verrou" => "I-PARAPHEUR"
            ],
            [
                "id_cf" => "9",
                "type_connecteur" => ConnecteurFrequence::TYPE_ENTITE,
                "famille_connecteur" => "TdT",
                "id_connecteur" => "",
                "id_ce" => "",
                "action_type" => "",
                "type_document" => "",
                "action" => "",
                "expression" => "10",
                "id_verrou" => ""
            ],
            [
                "id_cf" => "10",
                "type_connecteur" => ConnecteurFrequence::TYPE_GLOBAL,
                "famille_connecteur" => "TdT",
                "id_connecteur" => "",
                "id_ce" => "",
                "action_type" => "",
                "type_document" => "",
                "action" => "",
                "expression" => "1440",
                "id_verrou" => ""
            ],
            [
                "id_cf" => "11",
                "type_connecteur" => ConnecteurFrequence::TYPE_GLOBAL,
                "famille_connecteur" => "UndeliveredMail",
                "id_connecteur" => "",
                "id_ce" => "",
                "action_type" => "",
                "type_document" => "",
                "action" => "",
                "expression" => "1440",
                "id_verrou" => ""
            ]
        ];

        $this->assertEquals(
            json_encode($connectors),
            $result
        );
    }
}
