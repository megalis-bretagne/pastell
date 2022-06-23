<?php

class FakeSAE extends SAEConnecteur
{
    public const CONNECTEUR_ID = 'fakeSAE';
    private const FIXTURE = __DIR__ . '/fixtures/%s/';
    private const SEDA_VERSION = [
        1 => 'seda-1.0',
        2 => 'seda-2.1',
    ];
    private const FILENAME = [
        'ACK' => 'ACK.xml',
        'ATR' => 'ATR.xml',
        'ATR_refused' => 'ATR_refused.xml'
    ];

    private $tmpFile;

    /** @var DonneesFormulaire */
    private $collectiviteProperties;
    private $sedaVersion;

    public function __construct(TmpFile $tmpFile)
    {
        $this->tmpFile = $tmpFile;
    }

    public function setConnecteurConfig(DonneesFormulaire $collectiviteProperties)
    {
        $this->collectiviteProperties = $collectiviteProperties;
        $this->sedaVersion = $this->collectiviteProperties->get('seda_version') ?: 1;
    }

    private function getFixture(string $name): string
    {
        return sprintf(self::FIXTURE, self::SEDA_VERSION[$this->sedaVersion])
            . '/' . self::FILENAME[$name];
    }

    /**
     * @param $bordereauSEDA
     * @param $archivePath
     * @param string $file_type
     * @param string $archive_file_name
     * @return bool
     * @throws Exception
     */
    public function sendArchive($bordereauSEDA, $archivePath, $file_type = "TARGZ", $archive_file_name = "archive.tar.gz")
    {
        $this->collectiviteProperties->addFileFromData('last_bordereau', 'bordereau_seda.xml', $bordereauSEDA);
        $this->collectiviteProperties->addFileFromData('last_file', 'donnes.zip', file_get_contents($archivePath));
        if ($this->collectiviteProperties->get('result_send') == 2) {
            throw new UnrecoverableException("Ce connecteur bouchon est configuré pour renvoyer une erreur");
        }
        if ($this->collectiviteProperties->get('result_send') == 3) {
            header("Content-type: text/xml");
            echo $bordereauSEDA;
            exit;
        }
        return true;
    }

    public function getAck(string $transfert_id, string $originating_agency_id): string
    {
        return $this->getAcuseReception($transfert_id);
    }

    public function getAtr(string $transfert_id, string $originating_agency_id): string
    {
        return $this->getReply($transfert_id);
    }

    public function getAcuseReception($id_transfert)
    {

        $result_ack = $this->collectiviteProperties->get('result_ack') ?: 1;
        if ($result_ack == 2) {
            throw new Exception("Erreur provoquer par le bouchon SAE - code d'erreur HTTP : 500");
        }

        $simpleXMLWrapper = new SimpleXMLWrapper();
        $xml = $simpleXMLWrapper->loadFile($this->getFixture('ACK'));
        $xml->{'Date'} = date("c");
        $xml->{'MessageReceivedIdentifier'} = "$id_transfert";
        if ($this->sedaVersion == 1) {
            $xml->{'AcknowledgementIdentifier'}  = "ACK_" . mt_rand(0, mt_getrandmax());
        } else {
            $xml->{'MessageIdentifier'}  = "ACK_" . mt_rand(0, mt_getrandmax());
        }

        return $xml->asXML();
    }

    /**
     * @param $id_transfert
     * @param $atr_filepath
     * @return mixed
     * @throws SimpleXMLWrapperException
     */
    protected function getATRintern($id_transfert, $atr_filepath)
    {
        $simpleXMLWrapper = new SimpleXMLWrapper();
        $xml = $simpleXMLWrapper->loadFile($atr_filepath);
        $xml->{'Date'} = date("c");
        if ($this->sedaVersion == 1) {
            $xml->{'TransferIdentifier'} = "$id_transfert";
            $xml->{'TransferReplyIdentifier'}  = "ATR_" . mt_rand(0, mt_getrandmax());
            $xml->{'Archive'}->{'ArchivalAgencyArchiveIdentifier'} = mt_rand(0, mt_getrandmax());
        } else {
            $xml->{'MessageIdentifier'} = "$id_transfert";
            $xml->{'MessageRequestIdentifier'}  = "ATR_" . mt_rand(0, mt_getrandmax());
            $xml->{'DataObjectPackage'}->{'DescriptiveMetadata'}->{'ArchiveUnit'}
                ->{'Content'}->{'ArchivalAgencyArchiveUnitIdentifier'}
                = mt_rand(0, mt_getrandmax());
        }

        return $xml->asXML();
    }

    public function getReply($id_transfer)
    {
        $result_verif = $this->collectiviteProperties->get('result_verif') ?: 1;

        if ($result_verif == 1) {
            return $this->getATRintern($id_transfer, $this->getFixture('ATR'));
        }
        if ($result_verif == 2) {
            return $this->getATRintern($id_transfer, $this->getFixture('ATR_refused'));
        }

        throw new UnrecoverableException("Impossible de lire le message");
    }

    public function getURL($cote)
    {
        return "http://www.libriciel.fr";
    }

    public function generateArchive($bordereau, $tmp_folder)
    {

        $fileName = $this->tmpFile->create() . ".zip";

        $zip = new ZipArchive();

        if (! $zip->open($fileName, ZIPARCHIVE::CREATE)) {
            throw new UnrecoverableException("Impossible de créer le fichier d'archive : $fileName");
        }
        $has_file = false;
        foreach (scandir($tmp_folder) as $fileToAdd) {
            if (is_file("$tmp_folder/$fileToAdd")) {
                $zip->addFile("$tmp_folder/$fileToAdd", $fileToAdd);
                $has_file = true;
            }
        }

        if (! $has_file) {
            file_put_contents("$tmp_folder/empty", "");
            $zip->addFile("$tmp_folder/empty", "empty");
        }
        $zip->close();
        return $fileName;
    }

    public function getErrorString($number)
    {
    }
}
