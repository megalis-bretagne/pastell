<?php

class PastellBootstrap {

    private $adminControler;
    private $daemonManager;
    private $connecteurFactory;
    private $connecteurEntiteSQL;
    private $tmpFile;
    private $donneesFormulaireFactory;
    private $fluxEntiteSQL;

    public function __construct(
        AdminControler $adminControler,
        DaemonManager $daemonManager,
        ConnecteurFactory $connecteurFactory,
        ConnecteurEntiteSQL $connecteurEntiteSQL,
        TmpFile $tmpFile,
        DonneesFormulaireFactory $donneesFormulaireFactory,
        FluxEntiteSQL $fluxEntiteSQL
    ) {
        $this->adminControler = $adminControler;
        $this->daemonManager = $daemonManager;
        $this->connecteurFactory = $connecteurFactory;
        $this->connecteurEntiteSQL = $connecteurEntiteSQL;
        $this->tmpFile = $tmpFile;
        $this->donneesFormulaireFactory = $donneesFormulaireFactory;
        $this->fluxEntiteSQL = $fluxEntiteSQL;
    }

    public function bootstrap(UtilisateurObject $utilisateurObject){
        try {
            $this->createOrUpdateAdmin($utilisateurObject);
            $this->startDaemon();
            $this->installCertificate();
            $this->installHorodateur();
        } catch (Exception $e){
            $this->log("Erreur : " . $e->getMessage());
        }
    }

    private function createOrUpdateAdmin($utilisateurObject){
        $this->log("Création ou mise à jour de l'admin");
        $this->adminControler->createOrUpdateAdmin(
            $utilisateurObject,
            function ($message){
                $this->log($message);
            }
        );
    }

    private function startDaemon(){
        $this->log("Démon Pastell");
        $result =  $this->daemonManager->start() ? "Le démon est démarré\n" : "Le démon est arrêté\n";
        $this->log($result);
    }

    private function getHostname(){
        return parse_url(SITE_BASE,PHP_URL_HOST);
    }

    private function installCertificate(){
        if (file_exists("/etc/apache2/ssl/privkey.pem")){
            $this->log("Le certificat du site est déjà présent.");
            return;
        }

        $hostname = $this->getHostname();

        $letsencrypt_cert_path = "/etc/letsencrypt/live/$hostname";
        $privkey_path  = "$letsencrypt_cert_path/privkey.pem";
        $cert_path  = "$letsencrypt_cert_path/fullchain.pem";
        if (file_exists($privkey_path)){
            $this->log("Certificat letsencrypt trouvé !");
            symlink($privkey_path,"/etc/apache2/ssl/privkey.pem");
            symlink($cert_path,"/etc/apache2/ssl/fullchain.pem");
            return;
        }

        $script = __DIR__."/../ci-resources/generate-key-pair.sh";

        exec("$script $hostname",$output,$return_var);
        $this->log(implode("\n",$output));
        if ($return_var != 0){
            throw new Exception("Impossible de générer ou de trouver le certificat du site !");
        }
    }

    public function installHorodateur(){
        $connecteur = $this->connecteurFactory->getGlobalConnecteur('horodateur');
        if ($connecteur){
            $this->log("Le connecteur d'horodatage est configuré");
            return;
        }
        $this->log("Configuration d'un connecteur d'horodatage interne");
        $hostname = $this->getHostname();

        $key_file = $this->tmpFile->create();
        $cert_file = $this->tmpFile->create();

        $script = __DIR__."/../ci-resources/generate-timestamp-certificate.sh $hostname $key_file $cert_file 2>&1";

        exec("$script ",$output,$return_var);
        $this->log(implode("\n",$output));
        if ($return_var != 0){
            throw new Exception("Impossible de générer le certificat du timestamp !");
        }

        $id_ce =  $this->connecteurEntiteSQL->addConnecteur(0,'horodateur-interne','horodateur',"Horodateur interne par défaut");
        $donneesFormulaire = $this->donneesFormulaireFactory->getConnecteurEntiteFormulaire($id_ce);

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

        $this->fluxEntiteSQL->addConnecteur(0,'horodateur','horodateur',$id_ce);

        $this->log("Horodateur interne installé et configuré avec un nouveau certificat autosigné");
    }

    private function log($message){
        echo "[".date("Y-m-d H:i:s")."][Pastell bootstrap] $message\n";
    }

}