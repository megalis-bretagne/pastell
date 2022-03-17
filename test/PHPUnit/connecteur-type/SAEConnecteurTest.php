<?php

class SAEConnecteurTest extends PastellTestCase
{
    /**
     * @return SAEConnecteur
     * @throws ReflectionException
     */
    private function getSAEConnecteur()
    {
        return $this->getMockForAbstractClass(SAEConnecteur::class);
    }

    public function bordereauTransfertIdProvider()
    {
        return [
            [__DIR__ . "/fixtures/bordereau_seda_2.1.xml", '2020-05-12-ACTES-18'],
            [__DIR__ . "/fixtures/bordereau_seda_1.xml", 'e22753b25aab9aa523892312ee306e88'],
        ];
    }

    /**
     * @param string $bordereau_filepath
     * @param string $expected_transfert_id
     * @throws ReflectionException
     * @dataProvider bordereauTransfertIdProvider
     */
    public function testGetTransferIdForSEDA21(string $bordereau_filepath, string $expected_transfert_id)
    {
        $this->assertEquals(
            $expected_transfert_id,
            $this->getSAEConnecteur()->getTransferId(file_get_contents($bordereau_filepath))
        );
    }
}
