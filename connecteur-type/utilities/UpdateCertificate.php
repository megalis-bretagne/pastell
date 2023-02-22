<?php

class UpdateCertificate extends ConnecteurTypeActionExecutor
{
    /**
     * @return bool
     * @throws Exception
     */
    public function go()
    {
        $certificate_cert_pem = $this->getMappingValue('certificat_connexion_cert_pem');
        $certificate_key_pem = $this->getMappingValue('certificat_connexion_key_pem');
        $certificate_key_cert_pem = $this->getMappingValue('certificat_connexion_key_cert_pem');
        $certificate_connection = $this->getMappingValue('certificat_connexion');
        $certificate_password = $this->getMappingValue('certificat_password');

        $connecteur_properties = $this->getConnecteurProperties();

        $connecteur_properties->removeFile($certificate_cert_pem);
        $connecteur_properties->removeFile($certificate_key_pem);
        $connecteur_properties->removeFile($certificate_key_cert_pem);

        $pkcs12 = new PKCS12();
        $p12_data = $pkcs12->getAll(
            $connecteur_properties->getFilePath($certificate_connection),
            $connecteur_properties->get($certificate_password)
        );

        if ($p12_data) {
            $connecteur_properties->addFileFromData($certificate_cert_pem, $certificate_cert_pem, $p12_data['cert']);
            $connecteur_properties->addFileFromData($certificate_key_pem, $certificate_key_pem, $p12_data['pkey']);
            $connecteur_properties->addFileFromData(
                $certificate_key_cert_pem,
                $certificate_key_cert_pem,
                $p12_data['pkey'] . $p12_data['cert']
            );
        }


        $this->setLastMessage('Certificat à jour');
        return true;
    }
}
