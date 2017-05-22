<?php

class SFTP {

    private $netSFTP;
    private $sftpProperties;
    private $is_loggued = false;

    public function __construct(
        phpseclib\Net\SFTP $netSFTP,
        SFTPProperties $sftpProperties
    ) {
        $this->netSFTP = $netSFTP;
        $this->sftpProperties = $sftpProperties;
    }

    public function listDirectory($directory){
        $this->login();
        $result = $this->netSFTP->nlist($directory);
        $this->throwErrorIfNeeded();
        return $result;
    }

    public function get($remote_path,$local_path) {
        $this->login();
        if (! $this->netSFTP->get($remote_path,$local_path)) {
            $this->throwErrorIfNeeded();
        }
        return true;
    }

    public function put($remote_path,$local_path){
        $this->login();
        $this->netSFTP->put(
            $remote_path,
            $local_path,
            phpseclib\Net\SFTP::SOURCE_LOCAL_FILE
        );
        $this->throwErrorIfNeeded();
        return true;
    }


    public function delete($remote_path){
        $this->login();
        $this->netSFTP->delete($remote_path);
        $this->throwErrorIfNeeded();
        return true;
    }

    public function mkdir($remote_path){
        $this->login();
        $this->netSFTP->mkdir($remote_path);
        $this->throwErrorIfNeeded();
        return true;
    }

    private function login(){
        if ($this->is_loggued){
            return;
        }
        try {
            $this->netSFTP->login(
                $this->sftpProperties->login,
                $this->sftpProperties->password
            );
        } catch (Exception $e){
            throw new Exception($e->getMessage(),0,$e);
        }
        $server_fingerprint = $this->getFingerprint();
        if( $this->sftpProperties->verify_fingerprint && $server_fingerprint != $this->sftpProperties->fingerprint){
            throw new Exception(
                "L'empreinte du serveur ({$server_fingerprint}) ne correspond pas"
            );
        }
        $this->is_loggued = true;
    }

    private function throwErrorIfNeeded(){
        $errors = $this->netSFTP->getSFTPErrors();
        if ($errors && $errors[0]){
            throw new Exception($errors[0]);
        }
    }

    private function getFingerprint($flags =   SSH2_FINGERPRINT_SHA1 | SSH2_FINGERPRINT_HEX) {
        $hostkey = substr($this->netSFTP->getServerPublicHostKey(), 8);
        $hostkey = ($flags & 1) ? sha1($hostkey) : md5($hostkey);
        return ($flags & 2) ? pack('H*', $hostkey) : strtoupper($hostkey);
    }

}
