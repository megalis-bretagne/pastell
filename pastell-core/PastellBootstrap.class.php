<?php

class PastellBootstrap {

	private $pastellLogger;

	private $objectInstancier;

    public function __construct(ObjectInstancier $objectInstancier) {
    	$this->objectInstancier = $objectInstancier;
        $this->pastellLogger = $objectInstancier->getInstance(PastellLogger::class);
    }

    public function bootstrap(UtilisateurObject $utilisateurObject){
        try {
            $this->createOrUpdateAdmin($utilisateurObject);
            $this->startDaemon();
            $this->installCertificate();
            $this->installHorodateur();
            $this->installLibersign();
            $this->installCloudooo();
            $this->installConnecteurFrequenceDefault();
            $this->reduildTypeDossierPersonnalise();
            $this->flushRedis();
        } catch (Exception $e){
			$this->pastellLogger->emergency("Erreur : " . $e->getMessage());
			$this->pastellLogger->emergency($e->getTraceAsString());
        }
    }

    private function createOrUpdateAdmin($utilisateurObject){
		$this->pastellLogger->info("Création ou mise à jour de l'admin");
		$adminControler = $this->objectInstancier->getInstance(AdminControler::class);
		$adminControler->createOrUpdateAdmin(
            $utilisateurObject,
            function ($message){
				$this->pastellLogger->info($message);
            }
        );

    }

    private function startDaemon(){
		$this->pastellLogger->info("Démon Pastell");
		$daemonManager = $this->objectInstancier->getInstance(DaemonManager::class);
        $result =  $daemonManager->start() ? "Le démon est démarré\n" : "Le démon est arrêté\n";
		$this->pastellLogger->info($result);
    }

    private function getHostname(){
        return parse_url(SITE_BASE,PHP_URL_HOST);
    }

	/**
	 * @throws Exception
	 */
    private function installCertificate(){
        if (file_exists("/etc/apache2/ssl/privkey.pem")){
			$this->pastellLogger->info("Le certificat du site est déjà présent.");
            return;
        }

        $hostname = $this->getHostname();

        $letsencrypt_cert_path = "/etc/letsencrypt/live/$hostname";
        $privkey_path  = "$letsencrypt_cert_path/privkey.pem";
        $cert_path  = "$letsencrypt_cert_path/fullchain.pem";
        if (file_exists($privkey_path)){
			$this->pastellLogger->info("Certificat letsencrypt trouvé !");
            symlink($privkey_path,"/etc/apache2/ssl/privkey.pem");
            symlink($cert_path,"/etc/apache2/ssl/fullchain.pem");
            return;
        }

        $script = __DIR__ . "/../script/plateform-install/generate-key-pair.sh";

        exec("$script $hostname",$output,$return_var);
		$this->pastellLogger->info(implode("\n",$output));
        if ($return_var != 0){
            throw new UnrecoverableException("Impossible de générer ou de trouver le certificat du site !");
        }
    }

	/**
	 * @throws Exception
	 */
    public function installHorodateur(){
		$connecteurFactory = $this->objectInstancier->getInstance(ConnecteurFactory::class);
		$connecteur = $connecteurFactory->getGlobalConnecteur(Horodateur::CONNECTEUR_TYPE_ID);
        if ($connecteur){
			$this->pastellLogger->info("Le connecteur d'horodatage est configuré");
            return;
        }
		$this->pastellLogger->info("Configuration d'un connecteur d'horodatage interne");
        $hostname = $this->getHostname();

        $tmpFile = new TmpFile();
        $key_file = $tmpFile->create();
        $cert_file = $tmpFile->create();

        $script = __DIR__."/../ci-resources/generate-timestamp-certificate.sh $hostname $key_file $cert_file 2>&1";

        exec("$script ",$output,$return_var);
		$this->pastellLogger->info(implode("\n",$output));
        if ($return_var != 0){
            throw new UnrecoverableException("Impossible de générer le certificat du timestamp !");
        }
		$connecteurEntiteSQL = $this->objectInstancier->getInstance(ConnecteurEntiteSQL::class);
        $id_ce =  $connecteurEntiteSQL->addConnecteur(
        	0,
			'horodateur-interne',
			Horodateur::CONNECTEUR_TYPE_ID,
			"Horodateur interne par défaut"
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

		$fluxEntiteSQL = $this->objectInstancier->getInstance(FluxEntiteSQL::class);

		$fluxEntiteSQL->addConnecteur(0,'horodateur','horodateur',$id_ce);
        $this->fixConnecteurRight($id_ce);
		$this->pastellLogger->info("Horodateur interne installé et configuré avec un nouveau certificat autosigné");
    }

	/**
	 * @param string $server_name
	 * @throws Exception
	 */
    public function installCloudooo($server_name = "cloudooo"){
		$connecteurFactory = $this->objectInstancier->getInstance(ConnecteurFactory::class);
		$connecteur = $connecteurFactory->getGlobalConnecteur(ConvertisseurPDF::CONNECTEUR_TYPE_ID);
        if ($connecteur){
			$this->pastellLogger->info("Le connecteur de conversion Office vers PDF est configuré");
            return;
        }
		$connecteurEntiteSQL = $this->objectInstancier->getInstance(ConnecteurEntiteSQL::class);

		$id_ce =  $connecteurEntiteSQL->addConnecteur(
			0,
			'cloudooo',
			ConvertisseurPDF::CONNECTEUR_TYPE_ID,
			"Conversion Office PDF"
		);
		$donneesFormulaireFactory = $this->objectInstancier->getInstance(DonneesFormulaireFactory::class);

		$donneesFormulaire = $donneesFormulaireFactory->getConnecteurEntiteFormulaire($id_ce);
        $donneesFormulaire->setData('cloudooo_hostname',$server_name);
        $donneesFormulaire->setData('cloudooo_port','8011');
		$fluxEntiteSQL = $this->objectInstancier->getInstance(FluxEntiteSQL::class);
		$fluxEntiteSQL->addConnecteur(0,'convertisseur-office-pdf	','convertisseur-office-pdf',$id_ce);

        $this->fixConnecteurRight($id_ce);

		$this->pastellLogger->info("Le connecteur de conversion Office vers PDF a été configuré sur l'hote $server_name et le port 8011");
    }

    private function fixConnecteurRight($id_ce){
		$daemon_user = $this->objectInstancier->getInstance('daemon_user');
		$workspacePath = $this->objectInstancier->getInstance('workspacePath');

		$this->pastellLogger->info("Fix les droits sur les connecteur : chown " . $daemon_user);
        foreach (glob($workspacePath."/connecteur_$id_ce.yml*") as $file) {
            chown("$file", $daemon_user);
        }
    }

    public function installLibersign(){
        if (file_exists(__DIR__."/../web/libersign/update.json")){
			$this->pastellLogger->info("Libersign est déjà installé");
            return true;
        }
        if (empty(LIBERSIGN_INSTALLER)){
			$this->pastellLogger->info("Lien vers l'installeur de Libersign non trouvée");
            return true;
        }
        $this->majLibersign();
        return true;
    }

    public function majLibersign(){
        $make = file_get_contents(LIBERSIGN_INSTALLER);
        file_put_contents("/tmp/libersign_make.sh",$make);
        exec("/bin/bash /tmp/libersign_make.sh PROD",$output,$result);
    }

    public function installConnecteurFrequenceDefault(){
		$connecteurFrequenceSQL = $this->objectInstancier->getInstance(ConnecteurFrequenceSQL::class);

		$connecteurFrequence = new ConnecteurFrequence();
		$nearest = $connecteurFrequenceSQL->getNearestConnecteurFromConnecteur($connecteurFrequence);
		//Si aucune fréquence ne correspond à un connecteur par défaut
		if(!$nearest) {
			$connecteurFrequence->expression = "1";
			$connecteurFrequenceSQL->edit($connecteurFrequence);
			$this->pastellLogger->info("Initialisation d'un connecteur avec une fréquence de 1 minute");

			$connecteurFrequence->expression="10";
			$connecteurFrequence->type_connecteur=ConnecteurFrequence::TYPE_ENTITE;
			$connecteurFrequence->famille_connecteur='signature';
			$connecteurFrequence->id_connecteur='iParapheur';
			$connecteurFrequence->id_verrou="I-PARAPHEUR";
			$connecteurFrequenceSQL->edit($connecteurFrequence);
			$this->pastellLogger->info("Initialisation d'un connecteur avec une fréquence de 10 minute pour les i-Parapheur");
		}
	}

	/**
	 * @throws Exception
	 */
	public function reduildTypeDossierPersonnalise(){
		$typeDossierService = $this->objectInstancier->getInstance(TypeDossierService::class);
		$typeDossierService->rebuildAll();
	}

	public function flushRedis(){
		$this->pastellLogger->info("Vidage du cache");
		$redisWrapper = $this->objectInstancier->getInstance(MemoryCache::class);
		$redisWrapper->flushAll();
		$this->pastellLogger->info("Le cache a été vidé");
	}
}