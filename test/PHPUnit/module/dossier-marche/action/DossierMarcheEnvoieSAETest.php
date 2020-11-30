<?php

require_once __DIR__ . "/../../../../module/dossier-marche/action/DossierMarcheEnvoieSAE.class.php";

class DossierMarcheEnvoieSAETest extends PastellMarcheTestCase
{

    private $id_d;

    /**
     * @throws Exception
     */
    protected function setUp()
    {
        parent::setUp();
        $this->createConnecteurSEDA('dossier-marche');
        $this->createConnecteurSAE('dossier-marche');


        $result = $this->getInternalAPI()->post(
            "/Document/" . PastellTestCase::ID_E_COL,
            array('type' => 'dossier-marche')
        );
        $this->id_d = $result['id_d'];
    }

    /**
     * @throws Exception
     */
    public function testOK()
    {
        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($this->id_d);
        $donneesFormulaire->setTabData([
            "date_debut" => '1981-01-01',
            "date_fin" => '1982-07-12',
            "date_notification" => '1982-07-13',
            "numero_consultation" => "123456",
            "numero_marche" => "123",
            "code_cpv" => "200001_X",
            "type_consultation" =>  "MAPA",
            "type_marche" =>  "S",
            "infructueux" => 1,
            "recurrent" => 1,
            "attributaire" =>  "Libriciel SCOP\nAPI",
            "mot_cle" =>  "Mot clé 1\nMot clé 2",
            "libelle" =>  "Achat d'un bus logiciel",
            "contenu_versement" => "premiere partie"
        ]);

        $donneesFormulaire->addFileFromCopy('fichier_zip', 'archive.zip', __DIR__ . "/../fixtures/42007_achat_de_materiel_de_bureau.zip");


        $result = $this->getObjectInstancier()->getInstance('ActionExecutorFactory')->executeOnDocument(
            PastellTestCase::ID_E_COL,
            0,
            $this->id_d,
            DossierMarcheCommonEnvoieSAE::ACTION_NAME
        );

        if (! $result) {
            echo $donneesFormulaire->getFileContent('sae_bordereau');
            throw new Exception(($this->getObjectInstancier()->getInstance('ActionExecutorFactory')->getLastMessage()));
        }


        $xml = simplexml_load_file($donneesFormulaire->getFilePath('sae_bordereau'));
        $children = $xml->children(SedaValidation::SEDA_V_1_0_NS);


        $children->{'Date'} = 'NOT TESTABLE';
        $children->{'TransferIdentifier'} = "NOT TESTABLE";
        //file_put_contents(__DIR__."/../fixtures/bordereau.xml",$xml->asXML());

        $this->assertStringEqualsFile(__DIR__ . "/../fixtures/bordereau.xml", $xml->asXML());

        $donneesFormulaire->getFilePath('sae_bordereau');

        $sae_archive = $donneesFormulaire->getFileContent('sae_archive');

        $tmpFolder = new TmpFolder();
        $tmp_folder = $tmpFolder->create();
        file_put_contents("$tmp_folder/archive.tgz", $sae_archive);
        exec("tar xvzf $tmp_folder/archive.tgz -C $tmp_folder");

        $this->assertEquals(['.',
            '..',
            '1. Prparation',
            '11.4. Offres non retenues',
            '12.5. Envoi Contrleur Financier',
            '2. Lancement',
            '3. Analyses',
            'OFFRES DEMAT',
            'archive.tgz'], scandir("$tmp_folder/"));

        $this->assertEquals("non vide\n", file_get_contents("$tmp_folder/OFFRES DEMAT/ADULLACT/foo.txt"));

        $tmpFolder->delete($tmp_folder);
    }

    /**
     * @throws Exception
     */
    public function testNoDateNotification()
    {
        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($this->id_d);
        $donneesFormulaire->setTabData([
            "date_debut" => '1981-01-01',
            "date_fin" => '1982-07-12',
            "date_notification" => '', //Si pas de date de notif
            "numero_consultation" => "123456",
            "numero_marche" => "123",
            "code_cpv" => "200001_X",
            "type_consultation" =>  "MAPA",
            "type_marche" =>  "S",
            "infructueux" => 1,
            "recurrent" => 1,
            "attributaire" =>  "Libriciel SCOP\nAPI",
            "libelle" =>  "Achat d'un bus logiciel",
            "contenu_versement" => "premiere partie"
        ]);

        $donneesFormulaire->addFileFromCopy('fichier_zip', 'archive.zip', __DIR__ . "/../fixtures/42007_achat_de_materiel_de_bureau.zip");

        $result = $this->getObjectInstancier()->getInstance('ActionExecutorFactory')->executeOnDocument(
            PastellTestCase::ID_E_COL,
            0,
            $this->id_d,
            DossierMarcheCommonEnvoieSAE::ACTION_NAME
        );

        if (! $result) {
            echo $donneesFormulaire->getFileContent('sae_bordereau');
            throw new Exception(($this->getObjectInstancier()->getInstance('ActionExecutorFactory')->getLastMessage()));
        }


        $xml = simplexml_load_file($donneesFormulaire->getFilePath('sae_bordereau'));
        $children = $xml->children(SedaValidation::SEDA_V_1_0_NS);


        $children->{'Date'} = 'NOT TESTABLE';
        $children->{'TransferIdentifier'} = "NOT TESTABLE";
        //file_put_contents(__DIR__."/../fixtures/bordereau-no-date-notification.xml",$xml->asXML());

        $this->assertStringEqualsFile(__DIR__ . "/../fixtures/bordereau-no-date-notification.xml", $xml->asXML());
    }

    /**
     * @throws Exception
     */
    public function testWithMetadataJson()
    {
        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($this->id_d);
        $donneesFormulaire->setTabData([
            "date_debut" => '1981-01-01',
            "date_fin" => '1982-07-12',
            "date_notification" => '1982-07-13',
            "numero_consultation" => "123456",
            "numero_marche" => "123",
            "code_cpv" => "200001_X",
            "type_consultation" =>  "MAPA",
            "type_marche" =>  "S",
            "infructueux" => 1,
            "recurrent" => 1,
            "attributaire" =>  "Libriciel SCOP\nAPI",
            "libelle" =>  "Achat d'un bus logiciel",
            "contenu_versement" => "premiere partie"
        ]);

        $donneesFormulaire->addFileFromCopy('fichier_zip', 'archive.zip', __DIR__ . "/../fixtures/42007_achat_de_materiel_de_bureau.zip");
        $donneesFormulaire->addFileFromCopy('sae_config', 'config_sae.json', __DIR__ . "/../fixtures/config_sae.json");

        $result = $this->getObjectInstancier()->getInstance('ActionExecutorFactory')->executeOnDocument(
            PastellTestCase::ID_E_COL,
            0,
            $this->id_d,
            DossierMarcheCommonEnvoieSAE::ACTION_NAME
        );

        if (! $result) {
            echo $donneesFormulaire->getFileContent('sae_bordereau');
            throw new Exception(($this->getObjectInstancier()->getInstance('ActionExecutorFactory')->getLastMessage()));
        }


        $xml = simplexml_load_file($donneesFormulaire->getFilePath('sae_bordereau'));
        $children = $xml->children(SedaValidation::SEDA_V_1_0_NS);


        $children->{'Date'} = 'NOT TESTABLE';
        $children->{'TransferIdentifier'} = "NOT TESTABLE";

        $this->assertStringEqualsFile(__DIR__ . "/../fixtures/bordereau_metadata_json.xml", $xml->asXML());
    }
}
