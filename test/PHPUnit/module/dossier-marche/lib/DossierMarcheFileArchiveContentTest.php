<?php

class DossierMarcheFileArchiveContentTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @throws Exception
     * @throws UnrecoverableException
     */
    public function testExtact()
    {

        $dossierMarcheFileArchiveContent = new DossierMarcheFileArchiveContent();

        $info = $dossierMarcheFileArchiveContent->extract(__DIR__ . "/../fixtures/42007_achat_de_materiel_de_bureau.zip");

        $expected_result =  [
            'folder_name' => '42007_achat_de_materiel_de_bureau',
            'numero_marche' => '42007',
        ];

        $this->assertEquals($expected_result, $info);
    }
}
