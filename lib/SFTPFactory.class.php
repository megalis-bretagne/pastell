<?php

class SFTPFactory
{
    public const DEFAULT_HOST = 'localhost';
    public const DEFAULT_PORT = 22;

    public function getInstance(SFTPProperties $sftpProperties)
    {
        $netSFTP = new phpseclib\Net\SFTP(
            $sftpProperties->host ?: self::DEFAULT_HOST,
            $sftpProperties->port ?: self::DEFAULT_PORT,
            $sftpProperties->timeout
        );
        return new SFTP($netSFTP, $sftpProperties);
    }
}
