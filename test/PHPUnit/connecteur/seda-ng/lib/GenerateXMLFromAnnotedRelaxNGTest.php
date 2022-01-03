<?php

use PHPUnit\Framework\TestCase;

class GenerateXMLFromAnnotedRelaxNGTest extends TestCase
{
    public function testBigFile()
    {
        $generateXMLFromAnnotedRelaxNG = new GenerateXMLFromAnnotedRelaxNG(new RelaxNG());
        $new_relax_ng = $generateXMLFromAnnotedRelaxNG->generateFromRelaxNG(__DIR__ . "/../fixtures/EMEG_PROFIL_PES_0002_v1_with_annotation.rng");

        $relaxNG = new RelaxNG();
        $relax_ng = $relaxNG->getFromString($new_relax_ng);
        $relax_ng->registerXPathNamespace('seda', SedaValidation::SEDA_V_0_2_NS);
        $relax_ng->registerXPathNamespace('pastell', RelaxNgImportAgapeAnnotation::PASTELL_ANNOTATION_NS);
        $element = $relax_ng->xpath("//seda:Date/pastell:annotation")[0];

        $this->assertRegExp("#pastell:now#", (string) $element);
    }


    public function testBigFile2()
    {
        $generateXMLFromAnnotedRelaxNG = new GenerateXMLFromAnnotedRelaxNG(new RelaxNG());
        $profil = $generateXMLFromAnnotedRelaxNG->generateFromRelaxNG(
            __DIR__ . "/../fixtures/profil_test_annoted.rng"
        );

        $this->assertStringEqualsFile(__DIR__ . '/../fixtures/profil_test_annoted.xml', $profil);
    }
}
