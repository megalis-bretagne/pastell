<?php

class SAEConnecteurTest extends PastellTestCase
{
    /**
     * @return bool|SAEConnecteur
     */
    private function getSAEConnecteur()
    {
        $id_ce = $this->createConnector('as@lae-rest', "Asalae")['id_ce'];
        return $this->getConnecteurFactory()->getConnecteurById($id_ce);
    }

    public function testGetLastErrorCode()
    {
        $this->assertNull($this->getSAEConnecteur()->getLastErrorCode());
    }

    public function bordereauTransfertIdProvider()
    {
        return [
            [__DIR__ . "/fixtures/bordereau_seda_2.1.xml", '2020-05-12-ACTES-18'],
            [__DIR__ . "/fixtures/bordereau_seda_1.xml", 'e22753b25aab9aa523892312ee306e88'],
        ];
    }

    /**
     * @dataProvider bordereauTransfertIdProvider
     */
    public function testGetTransferIdForSEDA21($bordereau_filepath, $expected_transfert_id)
    {
        $this->assertEquals(
            $expected_transfert_id,
            $this->getSAEConnecteur()->getTransferId(file_get_contents($bordereau_filepath))
        );
    }
}
