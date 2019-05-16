<?php

class SFTPFactory {

    public function getInstance(SFTPProperties $sftpProperties){
        $netSFTP = new phpseclib\Net\SFTP(
            $sftpProperties->host,
            $sftpProperties->port?:22,
            $sftpProperties->timeout
        );
        return new SFTP($netSFTP,$sftpProperties);
    }

}
