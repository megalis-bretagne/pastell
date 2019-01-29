<?php
class UpdateCertificate extends ActionExecutor {

	/**
	 * @return bool
	 * @throws Exception
	 */
	public function go(){


        $connecteur_properties = $this->getConnecteurProperties();

        $connecteur_properties->removeFile("iparapheur_user_key_pem");
        $connecteur_properties->removeFile("iparapheur_user_certificat_pem");
        $connecteur_properties->removeFile("iparapheur_user_key_only_pem");


        $pkcs12 = new PKCS12();
		$p12_data = $pkcs12->getAll($connecteur_properties->getFilePath('iparapheur_user_certificat'),
										$connecteur_properties->get('iparapheur_user_certificat_password'));

		if (! $p12_data){
			$this->setLastMessage("Le certificat n'a pas pu être mis à jour car le mot de passe est manquant ou incorrect");
			return false;
		}

		if ($p12_data){
			$connecteur_properties->addFileFromData("iparapheur_user_key_pem","iparapheur_user_key_pem",$p12_data['pkey'].$p12_data['cert']);
			$connecteur_properties->addFileFromData("iparapheur_user_certificat_pem","iparapheur_user_certificat_pem",$p12_data['cert']); 
			$connecteur_properties->addFileFromData("iparapheur_user_key_only_pem","iparapheur_user_key_only_pem",$p12_data['pkey']);
		}

		/* Sous CENTOS, curl+nss ne supporte pas le PKCS#8, il faut donc avoir une clé déchiffré
			un bug est ouvert sur le bugzilla de redhat en état new depuis 2014...
			https://bugzilla.redhat.com/show_bug.cgi?id=1051533
		*/
		$unencrypted_pkey = $this->getUnencryptedKey($connecteur_properties->getFilePath('iparapheur_user_certificat'),
			$connecteur_properties->get('iparapheur_user_certificat_password'));

		if ($unencrypted_pkey) {
			$connecteur_properties->addFileFromData("iparapheur_user_key_only_pem", "iparapheur_user_key_only_pem", $unencrypted_pkey);
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