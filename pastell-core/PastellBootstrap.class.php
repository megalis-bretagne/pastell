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
        $hostname = parse_url(SITE_BASE,PHP_URL_HOST);
        $script = __DIR__."/../ci-resources/generate-key-pair.sh";

        exec("$script $hostname",$output,$return_var);
        $this->log(implode("\n",$output));
        if ($return_var != 0){
            throw new Exception("Impossible de générer ou de trouver le certificat du site !");
        }
    }


    private function installHorodateur(){
        #TODO installer l'horodateur interne
    }

    private function log($message){
        echo "[".date("Y-m-d H:i:s")."][Pastell bootstrap] $message\n";
    }

}