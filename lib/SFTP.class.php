<?php

class SFTP
{
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

    /**
     * @param $directory
     * @return mixed
     * @throws Exception
     */
    public function listDirectory($directory)
    {
        $this->login();
        $result = $this->netSFTP->nlist($directory);
        $this->throwErrorIfNeeded();
        return $result;
    }

    /**
     * @param $remote_path
     * @param $local_path
     * @return bool
     * @throws Exception
     */
    public function get($remote_path, $local_path)
    {
        $this->login();
        if (! $this->netSFTP->get($remote_path, $local_path)) {
            $this->throwErrorIfNeeded();
        }
        return true;
    }

    /**
     * @param $remote_path
     * @param $local_path
     * @return bool
     * @throws Exception
     */
    public function put($remote_path, $local_path)
    {
        $this->login();
        $this->netSFTP->put(
            $remote_path,
            $local_path,
            phpseclib\Net\SFTP::SOURCE_LOCAL_FILE
        );
        $this->throwErrorIfNeeded();
        return true;
    }

    /**
     * @param $from
     * @param $to
     * @return bool
     * @throws Exception
     */
    public function rename($from, $to)
    {
        $this->login();
        $this->netSFTP->rename($from, $to);
        $this->throwErrorIfNeeded();
        return true;
    }

    /**
     * @param $remote_path
     * @return bool
     * @throws Exception
     */
    public function delete($remote_path)
    {
        $this->login();
        $this->netSFTP->delete($remote_path);
        $this->throwErrorIfNeeded();
        return true;
    }

    /**
     * @param $remote_path
     * @return bool
     * @throws Exception
     */
    public function mkdir($remote_path)
    {
        $this->login();
        $this->netSFTP->mkdir($remote_path);
        $this->throwErrorIfNeeded();
        return true;
    }

    /**
     * @throws Exception
     */
    private function login()
    {
        $this->netSFTP->sftp_errors = array();
        if ($this->is_loggued) {
            return;
        }
        try {
            @ $error = $this->netSFTP->login(
                $this->sftpProperties->login,
                $this->sftpProperties->password
            );
            if ($error === false) {
                throw new Exception("Impossible de se connecter au serveur SFTP");
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 0, $e);
        }
        $server_fingerprint = $this->getFingerprint();
        if ($this->sftpProperties->verify_fingerprint && $server_fingerprint != $this->sftpProperties->fingerprint) {
            throw new Exception(
                "L'empreinte du serveur ({$server_fingerprint}) ne correspond pas"
            );
        }
        $this->is_loggued = true;
    }

    /**
     * @throws Exception
     */
    private function throwErrorIfNeeded()
    {
        $errors = $this->netSFTP->getSFTPErrors();
        if ($errors && $errors[0]) {
            throw new Exception($errors[0]);
        }
    }

    private function getFingerprint($flags = SSH2_FINGERPRINT_SHA1 | SSH2_FINGERPRINT_HEX)
    {
        $hostkey = substr($this->netSFTP->getServerPublicHostKey(), 8);
        $hostkey = ($flags & 1) ? sha1($hostkey) : md5($hostkey);
        return ($flags & 2) ? pack('H*', $hostkey) : strtoupper($hostkey);
    }

    /**
     * @param $file_or_directory
     * @return bool
     * @throws Exception
     */
    public function isDir($file_or_directory)
    {
        $this->login();
        $result = $this->netSFTP->is_dir($file_or_directory);
        $this->throwErrorIfNeeded();
        return $result;
    }

    /**
     * @param $file_or_directory
     * @return bool
     * @throws Exception
     */
    public function exists($file_or_directory)
    {
        $this->login();
        $this->throwErrorIfNeeded();
        return $this->netSFTP->file_exists($file_or_directory);
    }
}
