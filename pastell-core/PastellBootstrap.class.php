<?php

use Pastell\Service\Connecteur\ConnecteurCreationService;

class PastellBootstrap
{
    private $pastellLogger;

    private $objectInstancier;

    public function __construct(ObjectInstancier $objectInstancier)
    {
        $this->objectInstancier = $objectInstancier;
        $this->pastellLogger = $objectInstancier->getInstance(PastellLogger::class);
    }

    public function bootstrap(UtilisateurObject $utilisateurObject)
    {
        try {
            $this->createOrUpdateAdmin($utilisateurObject);
            $this->startDaemon();
            $this->installCertificate();
            $this->installHorodateur();
            $this->installCloudooo();
            $this->installPESViewerConnecteur();
            $this->installConnecteurFrequenceDefault();
            $this->rebuildTypeDossierPersonnalise();
            $this->flushRedis();
        } catch (Exception $e) {
            $this->pastellLogger->emergency("Erreur : " . $e->getMessage());
            $this->pastellLogger->emergency($e->getTraceAsString());
        }
    }

    private function createOrUpdateAdmin($utilisateurObject)
    {
        $this->pastellLogger->info("Création ou mise à jour de l'admin");
        $adminControler = $this->objectInstancier->getInstance(AdminControler::class);
        $adminControler->createOrUpdateAdmin(
            $utilisateurObject,
            function ($message) {
                $this->pastellLogger->info($message);
            }
        );
    }

    private function startDaemon()
    {
        $this->pastellLogger->info("Démon Pastell");
        $daemonManager = $this->objectInstancier->getInstance(DaemonManager::class);
        $result =  $daemonManager->start() ? "Le démon est démarré\n" : "Le démon est arrêté\n";
        $this->pastellLogger->info($result);
    }

    private function getHostname()
    {
        return parse_url(
            $this->objectInstancier->getInstance('site_base'),
            PHP_URL_HOST
        );
    }

    /**
     * @throws Exception
     */
    private function installCertificate()
    {
        if (file_exists("/data/certificate/privkey.pem")) {
            $this->pastellLogger->info("Le certificat du site est déjà présent.");
            return;
        }
        $hostname = $this->getHostname();

        $letsencrypt_cert_path = "/etc/letsencrypt/live/$hostname";
        $privkey_path  = "$letsencrypt_cert_path/privkey.pem";
        $cert_path  = "$letsencrypt_cert_path/fullchain.pem";
        if (file_exists($privkey_path)) {
            $this->pastellLogger->info("Certificat letsencrypt trouvé !");
            symlink($privkey_path, "/data/certificate/privkey.pem");
            symlink($cert_path, "/data/certificate/fullchain.pem");
            return;
        }

        $script = __DIR__ . "/../ci-resources/generate-key-pair.sh";

        exec("$script $hostname /data/certificate", $output, $return_var);
        $this->pastellLogger->info(implode("\n", $output));
        if ($return_var != 0) {
            throw new UnrecoverableException("Impossible de générer ou de trouver le certificat du site !");
        }
    }

    /**
     * @throws Exception
     */
    public function installHorodateur()
    {

        $connecteurCreationService = $this->objectInstancier->getInstance(ConnecteurCreationService::class);

        if ($connecteurCreationService->hasConnecteurGlobal(Horodateur::CONNECTEUR_TYPE_ID)) {
            $this->pastellLogger->info("Le connecteur d'horodatage est configuré");
            return;
        }

        $this->pastellLogger->info("Configuration d'un connecteur d'horodatage interne");
        $hostname = $this->getHostname();

        $tmpFile = new TmpFile();
        $key_file = $tmpFile->create();
        $cert_file = $tmpFile->create();

        $script = "bash " . __DIR__ . "/../ci-resources/generate-timestamp-certificate.sh $hostname $key_file $cert_file 2>&1";

        exec("$script ", $output, $return_var);
        $this->pastellLogger->info(implode("\n", $output));
        if ($return_var != 0) {
            throw new UnrecoverableException("Impossible de générer le certificat du timestamp !");
        }

        $id_ce = $connecteurCreationService->createAndAssociateGlobalConnecteur(
            'horodateur-interne',
            Horodateur::CONNECTEUR_TYPE_ID,
            'Horodateur interne par défaut'
        );

        $donneesFormulaireFactory = $this->objectInstancier->getInstance(DonneesFormulaireFactory::class);
        $donneesFormulaire = $donneesFormulaireFactory->getConnecteurEntiteFormulaire($id_ce);

        $donneesFormulaire->addFileFromCopy(
            'signer_certificate',
            'timestamp-certificate.pem',
            $cert_file
        );
        $donneesFormulaire->addFileFromCopy(
            'signer_key',
            'timestamp-key.pem',
            $key_file
        );
        $donneesFormulaire->addFileFromCopy(
            'ca_certificate',
            'timestamp-certificate.pem',
            $cert_file
        );

        $this->fixConnecteurRight($id_ce);
        $this->pastellLogger->info("Horodateur interne installé et configuré avec un nouveau certificat autosigné");
    }

    /**
     * @param string $server_name
     * @throws Exception
     */
    public function installCloudooo(string $server_name = "cloudooo")
    {
        $connecteurCreationService = $this->objectInstancier->getInstance(ConnecteurCreationService::class);

        if ($connecteurCreationService->hasConnecteurGlobal(ConvertisseurPDF::CONNECTEUR_TYPE_ID)) {
            $this->pastellLogger->info("Le connecteur de conversion Office vers PDF est configuré");
            return;
        }

        $id_ce = $connecteurCreationService->createAndAssociateGlobalConnecteur(
            'cloudooo',
            ConvertisseurPDF::CONNECTEUR_TYPE_ID,
            'Conversion Office PDF',
            [
                'cloudooo_hostname' => $server_name,
                'cloudooo_port' => '8011',
            ]
        );

        $this->fixConnecteurRight($id_ce);
        $this->pastellLogger->info("Le connecteur de conversion Office vers PDF a été configuré sur l'hote $server_name et le port 8011");
    }

    /**
     * @param string $url_pes_viewer
     * @throws Exception
     */
    public function installPESViewerConnecteur(string $url_pes_viewer = ""): void
    {
        if (! $url_pes_viewer) {
            $url_pes_viewer = $this->objectInstancier->getInstance('site_base');
        }

        $connecteurCreationService = $this->objectInstancier->getInstance(ConnecteurCreationService::class);

        if ($connecteurCreationService->hasConnecteurGlobal(PESViewer::CONNECTEUR_TYPE_ID)) {
            $this->pastellLogger->info("Le connecteur de PES viewer est déjà configuré");
            return;
        }

        $id_ce = $connecteurCreationService->createAndAssociateGlobalConnecteur(
            'pes-viewer',
            PESViewer::CONNECTEUR_TYPE_ID,
            '',
            ['url' => $url_pes_viewer]
        );

        $this->pastellLogger->info("Le connecteur de visualisation de PES a été installé sur l'URL $url_pes_viewer");
        $this->fixConnecteurRight($id_ce);
    }

    private function fixConnecteurRight($id_ce)
    {
        $daemon_user = $this->objectInstancier->getInstance('daemon_user');
        $workspacePath = $this->objectInstancier->getInstance('workspacePath');

        $this->pastellLogger->info("Fix les droits sur les connecteur : chown " . $daemon_user);
        foreach (glob($workspacePath . "/connecteur_$id_ce.yml*") as $file) {
            chown("$file", $daemon_user);
        }
    }

    public function installConnecteurFrequenceDefault()
    {
        $connecteurFrequenceSQL = $this->objectInstancier->getInstance(ConnecteurFrequenceSQL::class);

        $connecteurFrequence = new ConnecteurFrequence();
        $nearest = $connecteurFrequenceSQL->getNearestConnecteurFromConnecteur($connecteurFrequence);
        //Si aucune fréquence ne correspond à un connecteur par défaut
        if (!$nearest) {
            $defaultFrequencies = $this->getDefaultFrequencies();
            foreach ($defaultFrequencies as $name => $frequency) {
                $connecteurFrequence = new ConnecteurFrequence($frequency);
                $connecteurFrequenceSQL->edit($connecteurFrequence);
                $this->pastellLogger->info(
                    sprintf(
                        "Initialisation d'un connecteur `%s` avec la fréquence `%s`",
                        $name,
                        $frequency['expression']
                    )
                );
            }
        }
    }

    /**
     * @throws Exception
     */
    public function rebuildTypeDossierPersonnalise()
    {
        $typeDossierService = $this->objectInstancier->getInstance(TypeDossierService::class);
        $typeDossierService->rebuildAll();
    }

    public function flushRedis()
    {
        $this->pastellLogger->info("Vidage du cache");
        $redisWrapper = $this->objectInstancier->getInstance(MemoryCache::class);
        $redisWrapper->flushAll();
        $this->pastellLogger->info("Le cache a été vidé");
    }

    public function getDefaultFrequencies(): iterable
    {
        yield 'base' => [
            'expression' => '1',
        ];
        yield 'iparapheur' => [
            'expression' => '10',
            'type_connecteur' => ConnecteurFrequence::TYPE_ENTITE,
            'famille_connecteur' => 'signature',
            'id_connecteur' => 'iParapheur',
            'id_verrou' => "I-PARAPHEUR",
        ];
        yield 'purge' => [
            'expression' => '1440',
            'type_connecteur' => ConnecteurFrequence::TYPE_ENTITE,
            'famille_connecteur' => 'Purge',
            'id_connecteur' => 'purge',
            'id_verrou' => "PURGE",
        ];
        yield 'SAE' => [
            'expression' => '10',
            'type_connecteur' => ConnecteurFrequence::TYPE_ENTITE,
            'famille_connecteur' => 'SAE',
        ];
        yield 'SAE actes-generique (validation)' => [
            'expression' => "60 X 24\n1440",
            'type_connecteur' => ConnecteurFrequence::TYPE_ENTITE,
            'famille_connecteur' => 'SAE',
            'action_type' => ConnecteurFrequence::TYPE_ACTION_DOCUMENT,
            'type_document' => 'actes-generique',
            'action' => 'validation-sae',
        ];
        yield 'SAE actes-generique (vérification)' => [
            'expression' => "60 X 24\n1440",
            'type_connecteur' => ConnecteurFrequence::TYPE_ENTITE,
            'famille_connecteur' => 'SAE',
            'action_type' => ConnecteurFrequence::TYPE_ACTION_DOCUMENT,
            'type_document' => 'actes-generique',
            'action' => 'verif-sae',
        ];
        yield 'SAE helios-generique (validation)' => [
            'expression' => "60 X 24\n1440",
            'type_connecteur' => ConnecteurFrequence::TYPE_ENTITE,
            'famille_connecteur' => 'SAE',
            'action_type' => ConnecteurFrequence::TYPE_ACTION_DOCUMENT,
            'type_document' => 'helios-generique',
            'action' => 'validation-sae',
        ];
        yield 'SAE helios-generique (vérification)' => [
            'expression' => "60 X 24\n1440",
            'type_connecteur' => ConnecteurFrequence::TYPE_ENTITE,
            'famille_connecteur' => 'SAE',
            'action_type' => ConnecteurFrequence::TYPE_ACTION_DOCUMENT,
            'type_document' => 'helios-generique',
            'action' => 'verif-sae',
        ];
        yield 'tdt entité' => [
            'expression' => '10',
            'type_connecteur' => ConnecteurFrequence::TYPE_ENTITE,
            'famille_connecteur' => 'TdT',
        ];
        yield 'tdt global' => [
            'expression' => '1440',
            'type_connecteur' => ConnecteurFrequence::TYPE_GLOBAL,
            'famille_connecteur' => 'TdT',
        ];
        yield 'UndeliveredMail' => [
            'expression' => '1440',
            'type_connecteur' => ConnecteurFrequence::TYPE_GLOBAL,
            'famille_connecteur' => 'UndeliveredMail',
        ];
    }
}
