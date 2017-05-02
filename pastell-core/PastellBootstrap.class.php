<?php

class PastellBootstrap {

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
        if (file_exists("/etc/apache2/ssl/pastell_key.pem")){
            $this->log("Fichier pastell_key_pem trouvé: pas de création automatique");
            return;
        }
        $URL = parse_url(SITE_BASE,PHP_URL_HOST);
        `openssl genrsa -out /etc/apache2/ssl/pastell_key.pem 4096`;
        `/usr/bin/openssl req  -new -newkey rsa:4096 -days 3650 -nodes -subj "/C=FR/ST=HERAULT/L=MONTPELLIER/O=ADULLACT/OU=PASTELL/CN=$URL/emailAddress=pastell@$URL" -keyout /etc/apache2/ssl/pastell_key.pem -out /etc/apache2/ssl/pastell_csr.pem`;
        `/usr/bin/openssl x509 -req -days 3650 -in /etc/apache2/ssl/pastell_csr.pem -signkey /etc/apache2/ssl/pastell_key.pem -out /etc/apache2/ssl/pastell_cert.pem`;
        `/bin/chmod 400 /etc/apache2/ssl/pastell_key.pem`;
    }

    private function installHorodateur(){
        #TODO installer l'horodateur interne
    }

    private function log($message){
        echo "[".date("Y-m-d H:i:s")."][Pastell bootstrap] $message\n";
    }

}