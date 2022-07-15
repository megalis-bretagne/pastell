<?php

namespace Pastell\Helpers;

use SimpleXMLElement;
use UnrecoverableException;

class SedaHelper
{
    public const SEDA_0_2_NS = 'fr:gouv:ae:archive:draft:standard_echange_v0.2';
    public const SEDA_1_0_NS = 'fr:gouv:culture:archivesdefrance:seda:v1.0';
    public const SEDA_2_1_NS = 'fr:gouv:culture:archivesdefrance:seda:v2.1';

    /**
     * @param SimpleXMLElement $xml
     * @return string
     * @throws UnrecoverableException
     */
    public function getTransfertIdFromAck(SimpleXMLElement $xml): string
    {
        $xpath = [
            self::SEDA_0_2_NS =>  '/seda:ArchiveTransferReply/seda:TransferIdentifier',
            self::SEDA_1_0_NS =>  '/seda:Acknowledgement/seda:MessageReceivedIdentifier',
            self::SEDA_2_1_NS =>  '/seda:Acknowledgement/seda:MessageReceivedIdentifier'
        ];
        return $this->getElement($xml, $xpath, "Identifiant de transfert");
    }

    /**
     * @param SimpleXMLElement $xml
     * @return string
     * @throws UnrecoverableException
     */
    public function getTransfertIdFromAtr(SimpleXMLElement $xml): string
    {
        $xpath = [
            self::SEDA_0_2_NS =>  '/seda:ArchiveTransferAcceptance/seda:TransferIdentifier',
            self::SEDA_1_0_NS =>  '/seda:ArchiveTransferReply/seda:TransferIdentifier',
            self::SEDA_2_1_NS =>  '/seda:ArchiveTransferReply/seda:MessageIdentifier'
        ];
        return $this->getElement($xml, $xpath, "Identifiant de transfert");
    }

    /**
     * @param SimpleXMLElement $xml
     * @return string
     * @throws UnrecoverableException
     */
    public function getAckID(SimpleXMLElement $xml): string
    {
        $xpath = [
            self::SEDA_0_2_NS =>  '/seda:ArchiveTransferReply/seda:TransferReplyIdentifier',
            self::SEDA_1_0_NS =>  '/seda:Acknowledgement/seda:AcknowledgementIdentifier',
            self::SEDA_2_1_NS =>  '/seda:Acknowledgement/seda:MessageIdentifier'
        ];
        return $this->getElement($xml, $xpath, "Identifiant de l'acquittement");
    }

    /**
     * @param SimpleXMLElement $xml
     * @return string
     * @throws UnrecoverableException
     */
    public function getAtrID(SimpleXMLElement $xml): string
    {
        $xpath = [
            self::SEDA_0_2_NS =>  '/seda:ArchiveTransferAcceptance/seda:TransferAcceptanceIdentifier',
            self::SEDA_1_0_NS =>  '/seda:ArchiveTransferReply/seda:TransferReplyIdentifier',
            self::SEDA_2_1_NS =>  '/seda:ArchiveTransferReply/seda:MessageRequestIdentifier'
        ];
        return $this->getElement($xml, $xpath, "Identifiant de l'ATR");
    }

    /**
     * @param SimpleXMLElement $xml
     * @return string
     * @throws UnrecoverableException
     */
    public function getSAEArchivalIdentifierFromAtr(SimpleXMLElement $xml): string
    {
        $xpath = [
            self::SEDA_0_2_NS =>  '/seda:ArchiveTransferAcceptance/seda:Archive/seda:ArchivalAgencyArchiveIdentifier',
            self::SEDA_1_0_NS =>  '/seda:ArchiveTransferReply/seda:Archive/seda:ArchivalAgencyArchiveIdentifier',
            self::SEDA_2_1_NS =>  '/seda:ArchiveTransferReply/seda:DataObjectPackage/seda:DescriptiveMetadata' .
                '/seda:ArchiveUnit/seda:Content/seda:ArchivalAgencyArchiveUnitIdentifier',
        ];
        return $this->getElement($xml, $xpath, "Identifiant de l'archive sur le SAE");
    }

    private function getSedaNamespace(SimpleXMLElement $xml): string
    {
        return $xml->getNamespaces()[''];
    }

    /**
     * @param SimpleXMLElement $xml
     * @param array $xpath
     * @param string $type
     * @return string
     * @throws UnrecoverableException
     */
    private function getElement(SimpleXMLElement $xml, array $xpath, string $type): string
    {
        $ns = $this->getSedaNamespace($xml);
        if (empty($xpath[$ns])) {
            throw new UnrecoverableException(
                "Espace de nom $ns inconnu, -- $type -- : récupération  impossible"
            );
        }
        $xml->registerXPathNamespace('seda', $ns);
        $transfert_id_array = $xml->xpath($xpath[$ns]);
        if (count($transfert_id_array) !== 1) {
            throw new UnrecoverableException(
                "-- $type -- : Impossible de trouver l'élément $xpath[$ns]"
            );
        }
        return strval($transfert_id_array[0]);
    }

    public function getOriginatingAgency(SimpleXMLElement $xml): string
    {
        $xpath = [
            self::SEDA_0_2_NS =>  '/seda:ArchiveTransfer/seda:TransferringAgency/seda:Identification',
            self::SEDA_1_0_NS =>  '/seda:ArchiveTransfer/seda:TransferringAgency/seda:Identification',
            self::SEDA_2_1_NS =>  '/seda:ArchiveTransfer/seda:TransferringAgency/seda:Identifier'
        ];
        return $this->getElement($xml, $xpath, "Identifiant du service versant");
    }
}
