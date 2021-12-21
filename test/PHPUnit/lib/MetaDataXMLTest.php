<?php

class MetaDataXMLTest extends PHPUnit\Framework\TestCase
{
    public function testGet()
    {
        $metaDataXML = new MetaDataXML();

        $donneesFormulaire = $this->createMock("DonneesFormulaire");
        $donneesFormulaire->method('getRawData')->willReturn(array('id_facture_cpp' => 519450));


        $xml = $metaDataXML->getMetaDataAsXML($donneesFormulaire);
        $this->assertEquals($xml, "<?xml version=\"1.0\"?>
<flux>
  <data name=\"id_facture_cpp\" value=\"519450\"/>
</flux>
");
    }


    public function testGetFile()
    {
        $metaDataXML = new MetaDataXML();

        $donneesFormulaire = $this->createMock("DonneesFormulaire");
        $donneesFormulaire->method('getRawData')->willReturn(
            array(
                'id_facture_cpp' => 519450,
                'facture_pj_02' => array(
                    "PJ00FACQUAL_0000000000213700000000003457.pdf",
                    "PJ01FACQUAL_0000000000213700000000003457.pdf"
                )
            )
        );

        $xml = $metaDataXML->getMetaDataAsXML($donneesFormulaire);
        $this->assertEquals($xml, "<?xml version=\"1.0\"?>
<flux>
  <data name=\"id_facture_cpp\" value=\"519450\"/>
  <files name=\"facture_pj_02\">
    <file content=\"PJ00FACQUAL_0000000000213700000000003457.pdf\"/>
    <file content=\"PJ01FACQUAL_0000000000213700000000003457.pdf\"/>
  </files>
</flux>
");
    }
}
