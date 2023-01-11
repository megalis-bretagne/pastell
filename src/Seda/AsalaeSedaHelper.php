<?php

declare(strict_types=1);

namespace Pastell\Seda;

use Pastell\Helpers\SedaHelper;
use SimpleXMLElement;

final class AsalaeSedaHelper extends SedaHelper
{
    public function getSAEArchivalIdentifierFromAtrXpath(): array
    {
        return [
            self::SEDA_0_2_NS => ['/seda:ArchiveTransferAcceptance/seda:Archive/seda:ArchivalAgencyArchiveIdentifier'],
            self::SEDA_1_0_NS => ['/seda:ArchiveTransferReply/seda:Archive/seda:ArchivalAgencyArchiveIdentifier'],
            self::SEDA_2_1_NS => [
                '/seda:ArchiveTransferReply/seda:DataObjectPackage/seda:DescriptiveMetadata' .
                '/seda:ArchiveUnit/seda:Content/seda:ArchivalAgencyArchiveUnitIdentifier',
            ],
            self::SEDA_2_2_NS => [
                '/seda:ArchiveTransferReply/seda:DataObjectPackage/seda:DescriptiveMetadata' .
                '/seda:ArchiveUnit/seda:Content/seda:ArchivalAgencyArchiveUnitIdentifier',
            ],
        ];
    }

    public function getComment(SimpleXMLElement $xml): string
    {
        if ($xml->Comment) {
            return (string)$xml->Comment;
        }
        return '';
    }

    public function isSIPAccepted(SimpleXMLElement $xml): bool
    {
        $nodeName = $xml->getName();

        if ($nodeName === 'ArchiveTransferAcceptance') {
            //For SEDA V1
            return true;
        }

        if ($nodeName === 'ArchiveTransferReply' && $this->getReplyCode($xml) === '000') {
            return true;
        }
        return false;
    }
}
