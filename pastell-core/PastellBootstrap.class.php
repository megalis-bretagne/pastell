<?php

class PastellBootstrap {

    const PASTELL_SSL_KEY_FILE = '/etc/apache2/ssl/privkey.pem';
    const PASTELL_SSL_CERT_FILE = '/etc/apache2/ssl/fullchain.pem';

    private $adminControler;
    private $daemonManager;

    public function __construct(AdminControler $adminControler, DaemonManager $daemonManager) {
        $this->adminControler = $adminControler;
        $this->daemonManager = $daemonManager;
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
        $this->log("Tentative de démarage du démon");
        $result =  $this->daemonManager->start() ? "Le démon est démarré\n" : "Le démon est arrêté\n";
        $this->log($result);

    }

    private function installCertificate(){
        if (file_exists(self::PASTELL_SSL_KEY_FILE)){
            $this->log("Fichier ".self::PASTELL_SSL_KEY_FILE." trouvé: pas de création automatique");
            return;
        }
        $URL = parse_url(SITE_BASE,PHP_URL_HOST);
        $this->doCommand("openssl genrsa -out ".self::PASTELL_SSL_KEY_FILE." 4096");
        $this->doCommand("openssl req  -new -newkey rsa:4096 -days 3650 -nodes -subj '/C=FR/ST=HERAULT/L=MONTPELLIER/O=ADULLACT/OU=PASTELL/CN=$URL/emailAddress=pastell@$URL' -keyout "
            .self::PASTELL_SSL_KEY_FILE." -out /etc/apache2/ssl/pastell_csr.pem");
        $this->doCommand('/usr/bin/openssl x509 -req -days 3650 -in /etc/apache2/ssl/pastell_csr.pem -signkey '.self::PASTELL_SSL_KEY_FILE.' -out '.self::PASTELL_SSL_CERT_FILE);
        $this->doCommand('/bin/chmod 400 '.self::PASTELL_SSL_KEY_FILE);
    }

    private function doCommand(string $command){
        `$command`;
    }

    private function installHorodateur(){
        #TODO installer l'horodateur interne
    }

    private function log($message){
        echo "[".date("Y-m-d H:i:s")."][Pastell bootstrap] $message\n";
    }

}