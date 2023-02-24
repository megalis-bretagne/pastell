<?php

class TestConnexionRGS extends ActionExecutor
{
    public function go()
    {
        /** @var S2low $s2low */
        $s2low = $this->getMyConnecteur();
        $connecteur_properties = $this->getConnecteurProperties();

        try {
            $s2low->verifyForwardCertificate();
        } catch (Exception $e) {
            $this->setLastMessage($e->getMessage() .
                                    "<br/><br/>Certificat de connexion pastell : <br/><br/>" .
                                    nl2br($_SERVER['SSL_CLIENT_CERT']) .
                                    " <br/><br/>Certificat connecteur : <br/><br/>" . nl2br($connecteur_properties->getFileContent('forward_x509_certificate_pem')));
            return false;
        }

        $this->setLastMessage("La connexion est réussie");
        return true;
    }
}
