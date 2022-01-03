<?php

abstract class SAEConnecteur extends Connecteur
{
    /**
     * @param $bordereau
     * @param $tmp_folder
     * @return string
     * @throws Exception
     */
    public function generateArchive($bordereau, $tmp_folder)
    {
        $xml = simplexml_load_string($bordereau);

        $files_list = "";
        foreach ($xml->{'Integrity'} as $integrity_element) {
            $files_list .= escapeshellarg(strval($integrity_element->{'UnitIdentifier'})) . " ";
        }

        $archive_path = $tmp_folder . "/" . uniqid() . "_archive.tar.gz";

        $command = "tar cvzf $archive_path --directory $tmp_folder $files_list 2>&1";

        exec($command, $output, $return_var);

        if ($return_var != 0) {
            $output = implode("\n", $output);
            throw new Exception("Impossible de créer le fichier d'archive $archive_path - status : $return_var - output: $output");
        }

        return $archive_path;
    }

    public function getTransferId($bordereau)
    {
        $xml = simplexml_load_string($bordereau);
        if (! empty($xml->{'TransferIdentifier'})) {
            return strval($xml->{'TransferIdentifier'});
        } else {
            return strval($xml->{'MessageIdentifier'});
        }
    }

    abstract public function sendArchive($bordereauSEDA, $archivePath, $file_type = "TARGZ", $archive_file_name = "archive.tar.gz");

    /**
     * @throws UnrecoverableException
     * @throws Exception
     * @param $id_transfert
     * @return mixed
     * @deprecated 3.1.5 use self::getAck instead
     */
    abstract public function getAcuseReception($id_transfert);

    /**
     * @param string $transfert_id
     * @param string $originating_agency_id
     * @return string
     * @throws UnrecoverableException
     */
    abstract public function getAck(string $transfert_id, string $originating_agency_id): string;

    /**
     * @throws UnrecoverableException
     * @throws Exception
     * @param $id_transfer
     * @return mixed
     * @deprecated 3.1.5 use self::getAtr instead
     */
    abstract public function getReply($id_transfer);

    /**
     * @param string $transfert_id
     * @param string $originating_agency_id
     * @return string
     * @throws UnrecoverableException
     */
    abstract public function getAtr(string $transfert_id, string $originating_agency_id): string;


    abstract public function getURL($cote);

    abstract public function getErrorString($number);

    /**
     * @deprecated PA 3.0
     */
    public function getLastErrorCode()
    {
        return null;
    }
}
