<?php

declare(strict_types=1);

namespace Pastell\Helpers;

use SimpleXMLElement;
use UnrecoverableException;

abstract class SedaHelper
{
    public const SEDA_0_2_NS = 'fr:gouv:ae:archive:draft:standard_echange_v0.2';
    public const SEDA_1_0_NS = 'fr:gouv:culture:archivesdefrance:seda:v1.0';
    public const SEDA_2_1_NS = 'fr:gouv:culture:archivesdefrance:seda:v2.1';

    abstract public function getSAEArchivalIdentifierFromAtrXpath(): array;
    abstract public function getComment(SimpleXMLElement $xml): string;
    abstract public function isSIPAccepted(SimpleXMLElement $xml): bool;

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
     * @throws UnrecoverableException
     */
    protected function getElement(SimpleXMLElement $xml, array $xpath, string $type): string
    {
        $ns = $this->getSedaNamespace($xml);
        if (empty($xpath[$ns])) {
            throw new UnrecoverableException(
                "Espace de nom $ns inconnu, -- $type -- : récupération  impossible"
            );
        }
        $xml->registerXPathNamespace('seda', $ns);

        $element = null;
        foreach ($xpath[$ns] as $expression) {
            $elements = $xml->xpath($expression);
            if (\count($elements) === 1) {
                $element = (string)$elements[0];
                break;
            }
        }
        if ($element === null) {
            $elementList = \implode('`, `', $xpath[$ns]);
            throw new UnrecoverableException(
                "-- $type -- : Impossible de trouver les éléments `$elementList`"
            );
        }
        return $element;
    }

    /**
     * @throws UnrecoverableException
     */
    public function getTransfertIdFromAck(SimpleXMLElement $xml): string
    {
        $xpath = [
            self::SEDA_0_2_NS => ['/seda:ArchiveTransferReply/seda:TransferIdentifier'],
            self::SEDA_1_0_NS => ['/seda:Acknowledgement/seda:MessageReceivedIdentifier'],
            self::SEDA_2_1_NS => ['/seda:Acknowledgement/seda:MessageReceivedIdentifier'],
        ];
        return $this->getElement($xml, $xpath, 'Identifiant de transfert');
    }

    /**
     * @throws UnrecoverableException
     */
    public function getTransfertIdFromAtr(SimpleXMLElement $xml): string
    {
        $xpath = [
            self::SEDA_0_2_NS => ['/seda:ArchiveTransferAcceptance/seda:TransferIdentifier'],
            self::SEDA_1_0_NS => ['/seda:ArchiveTransferReply/seda:TransferIdentifier'],
            self::SEDA_2_1_NS => ['/seda:ArchiveTransferReply/seda:MessageIdentifier'],
        ];
        return $this->getElement($xml, $xpath, 'Identifiant de transfert');
    }

    /**
     * @throws UnrecoverableException
     */
    public function getAckID(SimpleXMLElement $xml): string
    {
        $xpath = [
            self::SEDA_0_2_NS => ['/seda:ArchiveTransferReply/seda:TransferReplyIdentifier'],
            self::SEDA_1_0_NS => ['/seda:Acknowledgement/seda:AcknowledgementIdentifier'],
            self::SEDA_2_1_NS => ['/seda:Acknowledgement/seda:MessageIdentifier'],
        ];
        return $this->getElement($xml, $xpath, "Identifiant de l'acquittement");
    }

    /**
     * @throws UnrecoverableException
     */
    public function getAtrID(SimpleXMLElement $xml): string
    {
        $xpath = [
            self::SEDA_0_2_NS => ['/seda:ArchiveTransferAcceptance/seda:TransferAcceptanceIdentifier'],
            self::SEDA_1_0_NS => ['/seda:ArchiveTransferReply/seda:TransferReplyIdentifier'],
            self::SEDA_2_1_NS => ['/seda:ArchiveTransferReply/seda:MessageRequestIdentifier'],
        ];
        return $this->getElement($xml, $xpath, "Identifiant de l'ATR");
    }

    /**
     * @throws UnrecoverableException
     */
    public function getSAEArchivalIdentifierFromAtr(SimpleXMLElement $xml): string
    {
        $xpath = $this->getSAEArchivalIdentifierFromAtrXpath();
        return $this->getElement($xml, $xpath, "Identifiant de l'archive sur le SAE");
    }

    /**
     * @throws UnrecoverableException
     */
    public function getOriginatingAgency(SimpleXMLElement $xml): string
    {
        $xpath = [
            self::SEDA_0_2_NS => ['/seda:ArchiveTransfer/seda:TransferringAgency/seda:Identification'],
            self::SEDA_1_0_NS => ['/seda:ArchiveTransfer/seda:TransferringAgency/seda:Identification'],
            self::SEDA_2_1_NS => ['/seda:ArchiveTransfer/seda:TransferringAgency/seda:Identifier'],
        ];
        return $this->getElement($xml, $xpath, 'Identifiant du service versant');
    }

    public function getReplyCode(SimpleXMLElement $xml): string
    {
        if ($xml->ReplyCode) {
            return (string)$xml->ReplyCode;
        }
        return '';
    }
}
