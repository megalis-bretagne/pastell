<?php
class UpdateCertificate extends ActionExecutor {
	
	public function go(){
		$connecteur_properties = $this->getConnecteurProperties();

        $connecteur_properties->removeFile("user_certificat_pem");
        $connecteur_properties->removeFile("user_key_pem");

		$pkcs12 = new PKCS12();
		$p12_data = $pkcs12->getAll($connecteur_properties->getFilePath('user_certificat'),
										$connecteur_properties->get('user_certificat_password'));
		
		if ($p12_data){
			$connecteur_properties->addFileFromData("user_certificat_pem","user_certificat_pem",$p12_data['cert']); 
			$connecteur_properties->addFileFromData("user_key_pem","user_key_pem",$p12_data['pkey']); 
		}

		/* Sous CENTOS, curl+nss ne supporte pas le PKCS#8, il faut donc avoir une clé déchiffré
			un bug est ouvert sur le bugzilla de redhat en état new depuis 2014...
			https://bugzilla.redhat.com/show_bug.cgi?id=1051533
		*/
		$unencrypted_pkey = $this->getUnencryptedKey($connecteur_properties->getFilePath('user_certificat'),
			$connecteur_properties->get('user_certificat_password'));

		if ($unencrypted_pkey) {
			$connecteur_properties->addFileFromData("user_key_pem", "user_key_pem", $unencrypted_pkey);
		}

		$this->setLastMessage("Certificat à jour");		
		return true;
	}

	/* Fonction à remonter dans le coeur Pastell*/
	public function getUnencryptedKey($p12_file_path,$p12_password){
		if (! file_exists($p12_file_path)){
			return false;
		}
		$pkcs12 = file_get_contents( $p12_file_path );
		$result = openssl_pkcs12_read( $pkcs12, $certs, $p12_password );

		if (! $result){
			return false;
		}
		return $certs['pkey'];
	}
	
}