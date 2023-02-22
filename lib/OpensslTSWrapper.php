<?php

class OpensslTSWrapper
{
    private $opensslPath;
    private $lastError;

    private $hash_algorithm;

    public function __construct($opensslPath)
    {
        $this->opensslPath = $opensslPath;
        $this->setHashAlgorithm('sha1');
    }

    public function setHashAlgorithm($hash_algorithm)
    {
        if (in_array($hash_algorithm, ['sha1','sha256',])) {
            $this->hash_algorithm = $hash_algorithm;
        }
    }

    public function getLastError()
    {
        return $this->lastError;
    }

    private function execute($command)
    {
        return shell_exec($command);
    }

    private function getTmpFile($data = "")
    {
        $file_path = sys_get_temp_dir()  . "/" . mt_rand();
        file_put_contents($file_path, $data);
        return $file_path;
    }

    public function getTimestampQuery($data)
    {
        $dataFilePath = $this->getTmpFile($data);
        $result = $this->execute($this->opensslPath . " ts -query -{$this->hash_algorithm} -data $dataFilePath -cert");
        unlink($dataFilePath);
        return $result;
    }

    public function getTimestampQueryString($timestampQuery)
    {
        $timestampQueryFilePath = $this->getTmpFile($timestampQuery);
        $result =  $this->execute($this->opensslPath . " ts -query -in $timestampQueryFilePath -text ");
        unlink($timestampQueryFilePath);
        return $result;
    }

    public function getTimestampReplyString($timestampReply)
    {
        $timestampReplyFilePath = $this->getTmpFile($timestampReply);
        $commande = $this->opensslPath . " ts -reply -in $timestampReplyFilePath -text " ;
        $result =  $this->execute($commande);
        unlink($timestampReplyFilePath);
        return $result;
    }


    public function verify($data, $timestampReply, $CAFilePath, $certFilePath, $configFile)
    {

        $dataFilePath = $this->getTmpFile($data);
        $timestampReplyFilePath = $this->getTmpFile($timestampReply);

        // redirect the output to /dev/null and get the shell return code
        // 0 means everything worked out
        $command =  $this->opensslPath . " ts -verify " .
                    " -data $dataFilePath " .
                    " -in $timestampReplyFilePath " .
                    " -CAfile $CAFilePath" .
                    " -untrusted $certFilePath " .
                    " -config " . $configFile .
                    " > /dev/null ; echo $?";

        $result =  trim($this->execute($command));

        unlink($dataFilePath);
        unlink($timestampReplyFilePath);

        $this->lastError = $result;
        return $result === "0";
    }

    public function createTimestampReply($timestampRequest, $signerCertificate, $signerKey, $signerKeyPassword, $configFile)
    {
        $timestampRequestFile = $this->getTmpFile($timestampRequest);
        $timestampReplyFile = $this->getTmpFile("");

        $command = $this->opensslPath . " ts -reply " .
                    " -queryfile $timestampRequestFile" .
                    " -signer " . $signerCertificate .
                    " -inkey " . $signerKey .
                    " -passin pass:" . $signerKeyPassword .
                    " -out $timestampReplyFile " .
                    " -config " . $configFile . " 2>&1";

        shell_exec($command);
        //TODO vérifier le retour

        $timestampReply = file_get_contents($timestampReplyFile);
        unlink($timestampRequestFile);
        unlink($timestampReplyFile);
        return $timestampReply;
    }
}
