<?php

declare(strict_types=1);

class SFTP
{
    private \phpseclib\Net\SFTP $netSFTP;
    private SFTPProperties $sftpProperties;
    private bool $isLogged = false;

    public function __construct(
        phpseclib\Net\SFTP $netSFTP,
        SFTPProperties $sftpProperties
    ) {
        $this->netSFTP = $netSFTP;
        $this->sftpProperties = $sftpProperties;
    }

    /**
     * @throws UnrecoverableException
     */
    public function listDirectory(string $directory): array
    {
        $this->login();
        $result = $this->netSFTP->nlist($directory);
        $this->throwErrorIfNeeded();
        return $result;
    }

    /**
     * @throws UnrecoverableException
     */
    public function get(string $remote_path, string $local_path): bool
    {
        $this->login();
        if (! $this->netSFTP->get($remote_path, $local_path)) {
            $this->throwErrorIfNeeded();
        }
        return true;
    }

    /**
     * @throws UnrecoverableException
     */
    public function put(string $remote_path, string $local_path): bool
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
     * @throws UnrecoverableException
     */
    public function rename(string $from, string $to): bool
    {
        $this->login();
        $this->netSFTP->rename($from, $to);
        $this->throwErrorIfNeeded();
        return true;
    }

    /**
     * @throws UnrecoverableException
     */
    public function delete(string $remote_path): bool
    {
        $this->login();
        $this->netSFTP->delete($remote_path);
        $this->throwErrorIfNeeded();
        return true;
    }

    /**
     * @throws UnrecoverableException
     */
    public function mkdir(string $remote_path): bool
    {
        $this->login();
        $this->netSFTP->mkdir($remote_path);
        $this->throwErrorIfNeeded();
        return true;
    }

    /**
     * @throws UnrecoverableException
     */
    private function login(): void
    {
        $this->netSFTP->sftp_errors = [];
        if ($this->isLogged) {
            return;
        }
        try {
            $error = $this->netSFTP->login(
                $this->sftpProperties->login,
                $this->sftpProperties->password
            );
            if ($error === false) {
                throw new UnrecoverableException('Impossible de se connecter au serveur SFTP');
            }
        } catch (Exception $e) {
            throw new UnrecoverableException($e->getMessage(), 0, $e);
        }
        $serverFingerprint = $this->getFingerprint();
        if ($this->sftpProperties->verifyFingerprint && $serverFingerprint !== $this->sftpProperties->fingerprint) {
            throw new UnrecoverableException(
                "L'empreinte du serveur ($serverFingerprint) ne correspond pas"
            );
        }
        $this->isLogged = true;
    }

    /**
     * @throws UnrecoverableException
     */
    private function throwErrorIfNeeded(): void
    {
        $errors = $this->netSFTP->getSFTPErrors();
        if ($errors && $errors[0]) {
            throw new UnrecoverableException($errors[0]);
        }
    }

    /**
     * @throws UnrecoverableException
     */
    private function getFingerprint(): string
    {
        $serverPublicHostKey = $this->netSFTP->getServerPublicHostKey();
        if ($serverPublicHostKey === null) {
            throw new UnrecoverableException('Impossible de récupérer la clé publique du serveur');
        }
        $hostKey = substr($serverPublicHostKey, 8);
        $hostKey = sha1($hostKey) ;
        return  strtoupper($hostKey);
    }

    /**
     * @throws Exception
     */
    public function isDir(string $file_or_directory): bool
    {
        $this->login();
        $result = $this->netSFTP->is_dir($file_or_directory);
        $this->throwErrorIfNeeded();
        return $result;
    }

    /**
     * @throws Exception
     */
    public function exists(string $file_or_directory): bool
    {
        $this->login();
        $this->throwErrorIfNeeded();
        return $this->netSFTP->file_exists($file_or_directory);
    }
}
