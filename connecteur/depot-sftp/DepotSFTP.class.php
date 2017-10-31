<?php

class DepotSFTP extends DepotConnecteur {


    const DEPOT_SFTP_HOST = 'depot_sftp_host';
    const DEPOT_SFTP_PORT = 'depot_sftp_port';
    const DEPOT_SFTP_PORT_DEFAULT = 22;
    const DEPOT_SFTP_LOGIN= 'depot_sftp_login';
    const DEPOT_SFTP_PASSWORD='depot_sftp_password';
    const DEPOT_SFTP_FINGERPRINT='depot_sftp_fingerprint';
    const DEPOT_SFTP_DIRECTORY = 'depot_sftp_directory';

    /** @var  SFTPFactory  */
    private $sftpFactory;

    /** @var  SFTP */
    private $sftp;

    public function __construct(SFTPFactory $SFTPFactory) {
        $this->sftpFactory = $SFTPFactory;
    }

    public function setConnecteurConfig(DonneesFormulaire $donneesFormulaire) {
        parent::setConnecteurConfig($donneesFormulaire);
        $sftpProperties = new SFTPProperties();
        $sftpProperties->host = $donneesFormulaire->get(self::DEPOT_SFTP_HOST);
        $sftpProperties->port = $donneesFormulaire->get(self::DEPOT_SFTP_PORT)?:self::DEPOT_SFTP_PORT_DEFAULT;
        $sftpProperties->login = $donneesFormulaire->get(self::DEPOT_SFTP_LOGIN);
        $sftpProperties->password = $donneesFormulaire->get(self::DEPOT_SFTP_PASSWORD);
        $sftpProperties->verify_fingerprint = true;
        $sftpProperties->fingerprint = $donneesFormulaire->get(self::DEPOT_SFTP_FINGERPRINT);
        $this->sftp = $this->sftpFactory->getInstance($sftpProperties);
    }

    public function listDirectory() {
        return $this->sftp->listDirectory($this->connecteurConfig->get(self::DEPOT_SFTP_DIRECTORY));

    }

    public function makeDirectory(string $directory_name) {
        $new_directory_name = $this->getAbsolutePath($directory_name);
        $this->sftp->mkdir($new_directory_name);
        return $new_directory_name;
    }

    public function saveDocument(string $directory_name, string $filename, string $filepath) {
        $new_filepath = $this->getAbsolutePath($directory_name,$filename);
        $this->sftp->put($new_filepath,$filepath);;
        return $new_filepath;
    }


    private function itemExists(string $item_name) {
        return array_reduce($this->listDirectory(),
            function($carry,$item) use($item_name){
                $carry = $carry || trim($item,"/") == $item_name;
                return $carry;
            }
        );
    }

    public function directoryExists(string $directory_name) {
        return $this->itemExists($directory_name);
    }

    public function fileExists(string $filename) {
        return $this->itemExists($filename);
    }
    private function getAbsolutePath($directory_or_file_name, $filename = false){
        $directory_or_file_name = $this->sanitizeFilename($directory_or_file_name);
        $result = rtrim($this->connecteurConfig->get(self::DEPOT_SFTP_DIRECTORY),"/")."/".$directory_or_file_name;
        if ($filename){
            $result .= "/".$this->sanitizeFilename($filename);
        }
        return $result;
    }

    private function sanitizeFilename($filename){
        return strtr($filename,"/","_");
    }
}