<?php

class ConnecteurDisponibleTest extends PastellTestCase
{
    /**
     * @return ConnecteurDisponible
     */
    private function getConnecteurDisponible()
    {
        return $this->getObjectInstancier()->getInstance('ConnecteurDisponible');
    }

    public function testGetConnecteurDisponible()
    {
        $result = $this->getConnecteurDisponible()->getList(1, 1, 'mailsec');
        $this->assertEquals("Mail securise", $result[0]['libelle']);
    }

    public function testGetConnecteurDisponibleNoRight()
    {
        $this->assertEmpty($this->getConnecteurDisponible()->getList(3, 1, 'mailsec'));
    }

    public function testGetConnecteurDisponibleInherited()
    {
        $result = $this->getConnecteurDisponible()->getList(1, 2, 'mailsec');
        $this->assertEquals("Mail securise", $result[0]['libelle']);
    }
}
