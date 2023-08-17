<?php

use Pastell\Service\Connecteur\ConnecteurCreationService;

class PastellBootstrap
{
    /**
     * TODO: ObjectInstancier should not be injected
     * We should have a collection of classes to bootstrap the app instead of a single which does everything
     */
    public function __construct(
        private readonly ObjectInstancier $objectInstancier,
        private readonly PastellLogger $pastellLogger,
    ) {
    }

    public function bootstrap(): void
    {
        try {
            $this->installCertificate();
            $this->installCertificate(true);
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

    private function getHostname(bool $isMailsec = false): string
    {
        return parse_url(
            $this->objectInstancier->getInstance($isMailsec ? 'websec_base' : 'site_base'),
            PHP_URL_HOST
        );
    }

    /**
     * TODO: Paths should be injected instead of being hardcoded
     * @throws UnrecoverableException
     */
    private function installCertificate(bool $isMailsec = false): void
    {
        if ($isMailsec) {
            $keyName = 'mailsec_privkey.pem';
            $certName = 'mailsec_fullchain.pem';
        } else {
            $keyName = 'privkey.pem';
            $certName = 'fullchain.pem';
        }

        $keyLocation = '/data/certificate/' . $keyName;
        $certLocation = '/data/certificate/' . $certName;

        if (file_exists($keyLocation)) {
            $this->pastellLogger->info('Le certificat du site est déjà présent.');
            return;
        }
        $hostname = $this->getHostname($isMailsec);

        $letsencrypt_cert_path = "/etc/letsencrypt/live/$hostname";
        $privkey_path  = "$letsencrypt_cert_path/privkey.pem";
        $cert_path  = "$letsencrypt_cert_path/fullchain.pem";
        if (file_exists($privkey_path)) {
            $this->pastellLogger->info('Certificat letsencrypt trouvé !');
            symlink($privkey_path, $keyLocation);
            symlink($cert_path, $certLocation);
            return;
        }

        $script = __DIR__ . '/../docker/generate-key-pair.sh';

        exec("$script $hostname /data/certificate $keyName $certName", $output, $return_var);
        $this->pastellLogger->info(implode("\n", $output));
        if ($return_var !== 0) {
            throw new UnrecoverableException('Impossible de générer ou de trouver le certificat du site !');
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

        $script = "bash " . __DIR__ . "/../docker/generate-timestamp-certificate.sh $hostname $key_file $cert_file 2>&1";

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
            'expression' => '10',
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
        yield 'tdt global' => [
            'expression' => '1440',
            'type_connecteur' => ConnecteurFrequence::TYPE_GLOBAL,
            'famille_connecteur' => 'TdT',
        ];
        yield 'cpp entité' => [
            'expression' => '30',
            'type_connecteur' => ConnecteurFrequence::TYPE_ENTITE,
            'famille_connecteur' => 'PortailFacture',
            'id_connecteur' => 'cpp',
            'action_type' => ConnecteurFrequence::TYPE_ACTION_CONNECTEUR,
            'id_verrou' => "CHORUS",
        ];
        yield 'cpp global' => [
            'expression' => '1440',
            'type_connecteur' => ConnecteurFrequence::TYPE_GLOBAL,
            'famille_connecteur' => 'PortailFacture',
        ];
        yield 'UndeliveredMail' => [
            'expression' => '1440',
            'type_connecteur' => ConnecteurFrequence::TYPE_GLOBAL,
            'famille_connecteur' => 'UndeliveredMail',
        ];
    }
}