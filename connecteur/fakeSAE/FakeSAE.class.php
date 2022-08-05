<?php

declare(strict_types=1);

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
        'ATR_refused' => 'ATR_refused.xml',
    ];

    private DonneesFormulaire $collectiviteProperties;
    private int $sedaVersion;
    private int $ackResult;
    private int $sendResult;

    public function setConnecteurConfig(DonneesFormulaire $collectiviteProperties)
    {
        $this->collectiviteProperties = $collectiviteProperties;
        $this->sedaVersion = (int)$this->collectiviteProperties->get('seda_version') ?: 1;
        $this->ackResult = (int)$this->collectiviteProperties->get('result_ack') ?: 1;
        $this->sendResult = (int)$this->collectiviteProperties->get('result_send') ?: 1;
    }

    private function getFixture(string $name): string
    {
        return sprintf(self::FIXTURE, self::SEDA_VERSION[$this->sedaVersion])
            . '/' . self::FILENAME[$name];
    }

    /**
     * @throws UnrecoverableException
     * @throws Exception
     */
    public function sendArchive(string $bordereau, string $archivePath): string
    {
        $this->collectiviteProperties->addFileFromData('last_bordereau', 'bordereau_seda.xml', $bordereau);
        $this->collectiviteProperties->addFileFromData('last_file', 'donnes.zip', file_get_contents($archivePath));
        if ($this->sendResult === 2) {
            throw new UnrecoverableException('Ce connecteur bouchon est configuré pour renvoyer une erreur');
        }
        if ($this->sendResult === 3) {
            header('Content-type: text/xml');
            echo $bordereau;
            exit;
        }
        return '<transfert identifier>';
    }

    public function provideAcknowledgment(): bool
    {
        return $this->ackResult !== 3;
    }

    /**
     * @throws SimpleXMLWrapperException
     */
    public function getAck(string $transfertId, string $originatingAgencyId): string
    {
        return $this->getAcuseReception($transfertId);
    }

    /**
     * @throws SimpleXMLWrapperException
     * @throws Exception
     */
    public function getAcuseReception(string $transfertId): string
    {
        if ($this->ackResult === 2) {
            throw new Exception("Erreur provoquer par le bouchon SAE - code d'erreur HTTP : 500");
        }

        $simpleXMLWrapper = new SimpleXMLWrapper();
        $xml = $simpleXMLWrapper->loadFile($this->getFixture('ACK'));
        $xml->Date = date('c');
        $xml->MessageReceivedIdentifier = $transfertId;
        if ($this->sedaVersion === 1) {
            $xml->AcknowledgementIdentifier = 'ACK_' . random_int(0, mt_getrandmax());
        } else {
            $xml->MessageIdentifier = 'ACK_' . random_int(0, mt_getrandmax());
        }

        return $xml->asXML();
    }

    /**
     * @throws UnrecoverableException
     * @throws SimpleXMLWrapperException
     */
    public function getAtr(string $transfertId, string $originatingAgencyId): string
    {
        $result_verif = (int)$this->collectiviteProperties->get('result_verif') ?: 1;

        if ($result_verif === 1) {
            return $this->getATRintern($transfertId, $this->getFixture('ATR'));
        }
        if ($result_verif === 2) {
            return $this->getATRintern($transfertId, $this->getFixture('ATR_refused'));
        }

        throw new UnrecoverableException('Impossible de lire le message');
    }

    /**
     * @throws SimpleXMLWrapperException
     * @throws Exception
     */
    protected function getATRintern(string $transfertId, string $atrFilepath): string
    {
        $simpleXMLWrapper = new SimpleXMLWrapper();
        $xml = $simpleXMLWrapper->loadFile($atrFilepath);
        $xml->Date = date('c');
        if ($this->sedaVersion === 1) {
            $xml->TransferIdentifier = $transfertId;
            $xml->TransferReplyIdentifier = 'ATR_' . random_int(0, mt_getrandmax());
            $xml->Archive->ArchivalAgencyArchiveIdentifier = random_int(0, mt_getrandmax());
        } else {
            $xml->MessageIdentifier = $transfertId;
            $xml->MessageRequestIdentifier = 'ATR_' . random_int(0, mt_getrandmax());
            $xml->DataObjectPackage->DescriptiveMetadata->ArchiveUnit
                ->Content->ArchivalAgencyArchiveUnitIdentifier = random_int(0, mt_getrandmax());
        }

        return $xml->asXML();
    }
}
