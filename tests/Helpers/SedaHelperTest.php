<?php

declare(strict_types=1);

namespace Pastell\Tests\Helpers;

use Pastell\Helpers\SedaHelper;
use PHPUnit\Framework\TestCase;
use UnrecoverableException;

class SedaHelperTest extends TestCase
{
    private function sedaHelper(): SedaHelper
    {
        return new SedaHelper();
    }

    public function ackProvider(): iterable
    {
        yield 'asalae' => [
            __DIR__ . '/fixtures/ack_asalae.xml',
            [
                'MessageIdentifier' => 'FRAD000_ACK_14',
                'MessageReceivedIdentifier' => '15ef78ef665a8777c33d1125783707f8dfb190f82869dc9248e46c5ed396d70b_1542893421',
            ]
        ];
    }

    public function atrProviderOk(): iterable
    {
        yield 'asalae OK' => [
            __DIR__ . '/fixtures/atr_asalae_ok.xml',
            [
                'MessageIdentifier' => '15ef78ef665a8777c33d1125783707f8dfb190f82869dc9248e46c5ed396d70b_1542893421',
                'MessageRequestIdentifier' => 'FRLSADC_ATR_27',
                'ArchiveUnitIdentifier' => 'AE_2022_15',
                'Comment' => 'Versement flux test pastell dev',
            ],
        ];
        yield 'vitam OK' => [
            __DIR__ . '/fixtures/atr_vitam_ok.xml',
            [
                'MessageIdentifier' => 'aeeaaaaaacfdatmhaahbiamc2a76zpyaaaaq',
                'MessageRequestIdentifier' => 'ef311f05-2a6f-40fe-b015-00b8ebeafcbd',
                'ArchiveUnitIdentifier' => 'aeaqaaaaaafdat3yadroqamc2a77c6yaaaba',
                'Comment' => '',
            ],
        ];
    }

    public function atrProviderKo(): iterable
    {
        yield 'asalae KO' => [
            __DIR__ . '/fixtures/atr_asalae_ko.xml',
            [
                'MessageIdentifier' => '1bc19af9-2212-4e5a-956a-36591c4ac27a',
                'MessageRequestIdentifier' => 'FRADCH_ATR_17388',
                'ArchiveUnitIdentifier' => '',
                'Comment' => "Transfert de l'acte administratif TRDYTVB",
            ],
        ];
        yield 'vitam KO' => [
            __DIR__ . '/fixtures/atr_vitam_ko.xml',
            [
                'MessageIdentifier' => 'aeeaaaaaacfdatmhabqumamcgwajmciaaaaq',
                'MessageRequestIdentifier' => '',
                'ArchiveUnitIdentifier' => 'AE_2022_15',
                'Comment' => "Échec du contrôle de cohérence entre le profil d'archivage déclaré dans le bordereau
                de transfert et celui déclaré dans le contrat d'entrée Detail= KO:1",
            ],
        ];
    }

    public function atrProvider(): iterable
    {
        yield from $this->atrProviderOk();
        yield from $this->atrProviderKo();
    }

    /**
     * @dataProvider atrProvider
     * @throws UnrecoverableException
     */
    public function testGetTransfertIdFromAtr(string $filepath, array $expected): void
    {
        $xml = \simplexml_load_string(\file_get_contents($filepath));
        static::assertSame($expected['MessageIdentifier'], $this->sedaHelper()->getTransfertIdFromAtr($xml));
    }

    /**
     * @dataProvider atrProvider
     * @throws UnrecoverableException
     */
    public function testGetAtrID(string $filepath, array $expected): void
    {
        $xml = \simplexml_load_string(\file_get_contents($filepath));
        static::assertSame($expected['MessageRequestIdentifier'], $this->sedaHelper()->getAtrID($xml));
    }

    /**
     * @dataProvider atrProviderOk
     * @throws UnrecoverableException
     */
    public function testGetSAEArchivalIdentifierFromAtr(string $filepath, array $expected): void
    {
        $xml = \simplexml_load_string(\file_get_contents($filepath));
        static::assertSame(
            $expected['ArchiveUnitIdentifier'],
            $this->sedaHelper()->getSAEArchivalIdentifierFromAtr($xml)
        );
    }

    /**
     * @dataProvider atrProvider
     * @throws UnrecoverableException
     */
    public function testGetComment(string $filepath, array $expected): void
    {
        $xml = \simplexml_load_string(\file_get_contents($filepath));
        static::assertSame($expected['Comment'], $this->sedaHelper()->getComment($xml));
    }

    /**
     * @dataProvider ackProvider
     * @throws UnrecoverableException
     */
    public function testGetTransfertIdFromAck(string $filepath, array $expected): void
    {
        $xml = \simplexml_load_string(\file_get_contents($filepath));
        static::assertSame($expected['MessageReceivedIdentifier'], $this->sedaHelper()->getTransfertIdFromAck($xml));
    }

    /**
     * @dataProvider ackProvider
     * @throws UnrecoverableException
     */
    public function testGetAckID(string $filepath, array $expected): void
    {
        $xml = \simplexml_load_string(\file_get_contents($filepath));
        static::assertSame($expected['MessageIdentifier'], $this->sedaHelper()->getAckID($xml));
    }
}
