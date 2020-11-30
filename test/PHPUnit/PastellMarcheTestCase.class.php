<?php

class PastellMarcheTestCase extends PastellTestCase
{


    public function reinitDatabase()
    {
        parent::reinitDatabase();
        $this->loadExtension(array(__DIR__ . "/../"));

        /** @var RoleSQL $roleSQL */
        $roleSQL = $this->getObjectInstancier()->getInstance('RoleSQL');

        $flux_id_list = [
            'pes-marche',
            'piece-marche',
            'piece-marche-par-etape',
            'dossier-marche'
        ];

        foreach ($flux_id_list as $id_flux) {
            $roleSQL->addDroit('admin', "$id_flux:lecture");
            $roleSQL->addDroit('admin', "$id_flux:edition");
        }
    }


    public function getNonPersistingDonneesFormulaire()
    {

        $documentTypeFactory = $this->getObjectInstancier()->getInstance("DocumentTypeFactory");

        $documentType = $documentTypeFactory->getFluxDocumentType('test');
        $filename = sys_get_temp_dir() . "/pastell_phpunit_non_persinting_donnees_formulaire";

        if (file_exists($filename)) {
            unlink($filename);
        }
        return new DonneesFormulaire($filename, $documentType);
    }

    protected function upload($flux_name, $info, $field, $filepath, $filenum = 0)
    {
        $filename = basename($filepath);
        $uploaded_file = $this->getEmulatedDisk() . "/tmp/$filename";
        copy($filepath, $uploaded_file);
        $result = $this->getInternalAPI()->post(
            "/Document/{$info['id_e']}/$flux_name/{$info['id_d']}/file/$field/$filenum",
            array('file_name' => $filename,
                'file_content' => file_get_contents($uploaded_file))
        );
        return $result;
    }

    /**
     * @param $id_flux
     * @return ParametrageFluxPieceMarche
     */
    protected function createConnecteurParametragePieceMarche($id_flux)
    {

        $result = $this->getInternalAPI()->post(
            "/entite/" . self::ID_E_COL . "/connecteur",
            array('libelle' => 'Paramétrage flux Pieces de marché' , 'id_connecteur' => 'parametrage-flux-piece-marche')
        );

        $id_ce = $result['id_ce'];

        $this->getInternalAPI()->patch(
            "/entite/" . self::ID_E_COL . "/connecteur/$id_ce/content/  ",
            array()
        );

        $this->getInternalAPI()->post(
            "/entite/" . self::ID_E_COL . "/flux/$id_flux/connecteur/$id_ce",
            array('type' => 'ParametragePieceMarche')
        );

        $connecteurFactory = $this->getObjectInstancier()->getInstance('ConnecteurFactory');
        /** @var ParametrageFluxPieceMarche $parametragePieceMarche */
        $parametragePieceMarche = $connecteurFactory->getConnecteurById($id_ce);
        $parametragePieceMarche->setPieceMarcheJsonByDefault();

        return $parametragePieceMarche;
    }


    /**
     * @param string $id_flux
     * @param int $id_e
     * @return string
     */
    public function createDocument($id_flux, $id_e = PastellTestCase::ID_E_COL)
    {
        $result = $this->getInternalAPI()->post(
            "/Document/$id_e",
            array('type' => $id_flux)
        );
        return $result['id_d'];
    }

    public function documentAction($id_d, $action)
    {
        return $this->getObjectInstancier()->getInstance('ActionExecutorFactory')->executeOnDocument(
            PastellTestCase::ID_E_COL,
            0,
            $id_d,
            $action
        );
    }

    protected function assertLastAction($id_d, $last_action)
    {
        $this->assertEquals(
            $last_action,
            $this->getObjectInstancier()->getInstance('DocumentActionEntite')->getInfo($id_d, self::ID_E_COL)['last_action']
        );
    }

    protected function assertLastMessage($last_message)
    {
        $this->assertEquals(
            $last_message,
            $this->getObjectInstancier()->getInstance('ActionExecutorFactory')->getLastMessage()
        );
    }

    /**
     * @param string $id_flux
     * @return int $id_ce
     * @throws Exception
     */
    protected function createConnecteurSEDA($id_flux)
    {
        $profil_file_path = __DIR__ . "/../profil/";
        $fixtures_path = __DIR__ . "/profil/fixtures/";

        $seda_definition_file = [

            'dossier-marche' => [
                'schema_rng_path'  => "PROFIL_DOSSIERS_MARCHES_LS_schema.rng",
                'agape_file_path' => "PROFIL_DOSSIERS_MARCHES_LS.xml",
                'flux_content_path' => "PROFIL_DOSSIERS_MARCHES_LS.json"
            ],
            'piece-marche' => [
                'schema_rng_path'  => "PROFIL_PIECES_MARCHES_LS.rng",
                'agape_file_path' => "PROFIL_PIECES_MARCHES_LS.xml",
                'flux_content_path' => "PROFIL_PIECES_MARCHES_LS.json"
            ],

            'pes-marche' => [
                'schema_rng_path'  => "Profil_PES_Marche_LS_V1.rng",
                'agape_file_path' => "Profil_PES_Marche_LS_V1.xml",
                'flux_content_path' => "Profil_PES_Marche_LS_V1.json"
            ],
            
        ];
        if (empty($seda_definition_file[$id_flux])) {
            throw new Exception("Unable to find SEDA files for $id_flux");
        }

        $file_def = $seda_definition_file[$id_flux];
        return $this->createConnecteurSEDAInternal(
            $profil_file_path . "/" . $file_def['schema_rng_path'],
            $profil_file_path . "/" . $file_def['agape_file_path'],
            $fixtures_path . "/" . $file_def['flux_content_path'],
            $id_flux
        );
    }

    private function createConnecteurSEDAInternal($schema_rng_path, $agape_file_path, $flux_content_path, $id_flux)
    {
        $result = $this->getInternalAPI()->post(
            "/entite/" . self::ID_E_COL . "/connecteur",
            array('libelle' => 'SEDA NG' , 'id_connecteur' => 'seda-ng')
        );

        $id_ce = $result['id_ce'];


        $this->getInternalAPI()->patch(
            "/entite/" . self::ID_E_COL . "/connecteur/$id_ce/content/  ",
            array()
        );


        $this->getInternalAPI()->post(
            "/entite/" . self::ID_E_COL . "/connecteur/$id_ce/file/schema_rng",
            array('file_name' => basename($schema_rng_path),
                'file_content' => file_get_contents($schema_rng_path))
        );

        $this->getInternalAPI()->post(
            "/entite/" . self::ID_E_COL . "/connecteur/$id_ce/file/profil_agape",
            array('file_name' => basename($agape_file_path),
                'file_content' => file_get_contents($agape_file_path))
        );

        $this->getInternalAPI()->post(
            "/entite/" . self::ID_E_COL . "/connecteur/$id_ce/file/flux_info_content",
            array('file_name' => basename($flux_content_path),
                'file_content' => file_get_contents($flux_content_path))
        );
        $this->getInternalAPI()->post(
            "/entite/" . self::ID_E_COL . "/connecteur/$id_ce/file/connecteur_info_content",
            array('file_name' => basename($flux_content_path),
                'file_content' => file_get_contents($flux_content_path))
        );

        $this->getInternalAPI()->post(
            "/entite/" . self::ID_E_COL . "/flux/$id_flux/connecteur/$id_ce",
            array('type' => 'Bordereau SEDA')
        );

        return $id_ce;
    }

    /**
     * @param $flux_id
     */
    protected function createConnecteurSAE($flux_id)
    {

        $result = $this->getInternalAPI()->post(
            "/entite/" . self::ID_E_COL . "/connecteur",
            array('libelle' => 'SAE' , 'id_connecteur' => 'fakeSAE')
        );

        $id_ce = $result['id_ce'];

        $this->getInternalAPI()->post(
            "/entite/" . self::ID_E_COL . "/flux/$flux_id/connecteur/$id_ce",
            array('type' => 'SAE')
        );
    }
}
