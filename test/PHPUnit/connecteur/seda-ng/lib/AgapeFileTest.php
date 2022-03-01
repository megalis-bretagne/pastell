<?php

class AgapeFileTest extends PHPUnit\Framework\TestCase
{
    public function testGetAnnotation()
    {
        $agapeFile = new AgapeFile();
        $annotation = $agapeFile->getAllAnnotation(__DIR__ . "/../fixtures/EMEG_PROFIL_PES_0002_v1.5.xml");
        $this->assertMatchesRegularExpression("#Transfert des flux comptables#", (string) $annotation[0]);
    }
}
