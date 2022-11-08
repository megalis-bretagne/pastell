<?php

//Lib à utiliser : https://github.com/Nicolab/php-ftp-client

// Docker pour tester
// docker run -itd --rm -p 59021:21 -e FTP_USER=scott -e FTP_PASS=tiger -e HOST=192.168.1.24 -p 59000-59004:59000-59004 -e PASV_MIN_PORT=59000 -e PASV_MAX_PORT=59004 mcreations/ftp


class DepotFTP extends DepotConnecteur
{
    public const DEPOT_FTP_HOST = 'depot_ftp_host';
    public const DEPOT_FTP_PORT = 'depot_ftp_port';
    public const DEPOT_FTP_SSL = 'depot_ftp_ssl';
    public const DEPOT_FTP_LOGIN = 'depot_ftp_login';
    public const DEPOT_FTP_PASSWORD = 'depot_ftp_password';
    public const DEPOT_FTP_PASSIVE_MODE = 'depot_ftp_passive_mode';
    public const DEPOT_FTP_DIRECTORY = 'depot_ftp_directory';

    public const DEPOT_FTP_PORT_DEFAULT = '21';

    /** @var FtpClientWrapper  */
    private $ftpClient;

    private $isLoggued = false;

    public function setFtpClient(FtpClientWrapper $ftpClient)
    {
        $this->ftpClient = $ftpClient;
    }

    /**
     * @return \FtpClient\FtpClient
     */
    private function getFtpClient()
    {
        if ($this->isLoggued) {
            return $this->ftpClient;
        }
        if ($this->ftpClient === null) {
            $this->ftpClient = new FtpClientWrapper();
        }
        $this->ftpClient->close();
        $this->ftpClient->connect(
            $this->connecteurConfig->get(self::DEPOT_FTP_HOST),
            (bool) $this->connecteurConfig->get(self::DEPOT_FTP_SSL),
            $this->connecteurConfig->get(self::DEPOT_FTP_PORT) ?: self::DEPOT_FTP_PORT_DEFAULT
        );
        $this->ftpClient->login(
            $this->connecteurConfig->get(self::DEPOT_FTP_LOGIN),
            $this->connecteurConfig->get(self::DEPOT_FTP_PASSWORD)
        );
        if ($this->connecteurConfig->get(self::DEPOT_FTP_PASSIVE_MODE)) {
            $this->ftpClient->pasv(true);
        }
        $this->isLoggued = true;
        return $this->ftpClient;
    }


    public function listDirectory()
    {
        return $this->getFtpClient()->nlist($this->connecteurConfig->get(self::DEPOT_FTP_DIRECTORY));
    }

    private function getAbsolutePath($directory_or_file_name, $filename = false)
    {
        $directory_or_file_name = $this->sanitizeFilename($directory_or_file_name);
        $result = rtrim($this->connecteurConfig->get(self::DEPOT_FTP_DIRECTORY), "/") . "/" . $directory_or_file_name;
        if ($filename) {
            $result .= "/" . $this->sanitizeFilename($filename);
        }
        return $result;
    }

    private function sanitizeFilename($filename)
    {
        return strtr($filename, "/", "_");
    }

    public function makeDirectory(string $directory_name)
    {
        $directory_path = $this->getAbsolutePath($directory_name);
        $this->getFtpClient()->mkdir($directory_path);
        return $directory_path;
    }

    public function saveDocument(string $directory_name, string $filename, string $filepath)
    {
        $new_filepath = $this->getAbsolutePath($directory_name, $filename);
        $this->getFtpClient()->put($new_filepath, $filepath, FTP_BINARY);
        return $new_filepath;
    }

    private function itemExists(string $item_name)
    {
        return array_reduce(
            $this->listDirectory(),
            function ($carry, $item) use ($item_name) {
                $carry = $carry || basename($item) == $item_name;
                return $carry;
            }
        );
    }

    public function directoryExists(string $directory_name)
    {
        return $this->itemExists($directory_name);
    }

    public function fileExists(string $filename)
    {
        return $this->itemExists($filename);
    }
}
