<?php

class RelaxNGImportAnnotationTest extends PHPUnit\Framework\TestCase
{
    private function getNewRelaxNG($agape_file, $rng_file)
    {
        $relax_ng_orig = $rng_file;

        $relaxNGImportAnnotation = new RelaxNgImportAgapeAnnotation();
        $new_relax_ng = $relaxNGImportAnnotation->importAnnotation(
            $relax_ng_orig,
            $agape_file
        );

        $relaxNG = new RelaxNG();
        $relax_ng = $relaxNG->getFromString($new_relax_ng);
        if (! $relax_ng) {
            print_r(libxml_get_errors());
            throw new Exception("Impossible de lire de fichier RNG");
        }

        $relax_ng->registerXPathNamespace('rng', RelaxNgImportAgapeAnnotation::RELAX_NG_NS);
        $relax_ng->registerXPathNamespace('pastell', RelaxNgImportAgapeAnnotation::PASTELL_ANNOTATION_NS);
        return $relax_ng;
    }

    public function testImportAnnotation()
    {
        $relax_ng = $this->getNewRelaxNG(
            __DIR__ . "/../fixtures/EMEG_PROFIL_PES_0002_v1.5.xml",
            __DIR__ . "/../fixtures/EMEG_PROFIL_PES_0002_v1_schema.rng"
        );
        $element = $relax_ng->xpath("//rng:element[@name='Date']/pastell:annotation")[0];
        $this->assertMatchesRegularExpression("#pastell:now#", (string) $element);
    }

    public function testImportAnnotation2()
    {
        $relax_ng = $this->getNewRelaxNG(
            __DIR__ . "/../fixtures/profil_test.xml",
            __DIR__ . "/../fixtures/profil_test_schema.rng"
        );
        $element = $relax_ng->xpath("//rng:element[@name='Date']/pastell:annotation")[0];
        $this->assertMatchesRegularExpression("#pastell:now#", (string) $element);
    }

    public function testPesManyAnnotationOnKeywords()
    {
        $relaxNGImportAnnotation = new RelaxNgImportAgapeAnnotation();
        $new_relax_ng = $relaxNGImportAnnotation->importAnnotation(
            __DIR__ . "/../fixtures/many_keywords_schema.rng",
            __DIR__ . "/../fixtures/many_keywords.xml"
        );

        $this->assertMatchesRegularExpression("#Ceci est une annotation#", $new_relax_ng);
        $this->assertMatchesRegularExpression("#Ceci est une seconde annotation#", $new_relax_ng);
    }

    public function testAnnotationForTwoSiblingWithSameName()
    {
        $relaxNGImportAgapeAnnotation = new RelaxNgImportAgapeAnnotation();

        $relaxNG_with_annotation = $relaxNGImportAgapeAnnotation->importAnnotation(
            __DIR__ . "/../fixtures/bordereau-2-document_schema.rng",
            __DIR__ . "/../fixtures/bordereau-2-document.xml"
        );

        $generateXMLFromAnnotedRelaxNG = new GenerateXMLFromAnnotedRelaxNG(new RelaxNG());

        $bordereau_seda_with_annotation = $generateXMLFromAnnotedRelaxNG->generateFromRelaxNGString($relaxNG_with_annotation);

        $xml = simplexml_load_string($bordereau_seda_with_annotation);

        $archiveNode = $xml->children(SedaValidation::SEDA_V_1_0_NS)->Archive;
        /** @var SimpleXMLElement $document0 */
        $document0 =  $archiveNode->Document[0];
        /** @var SimpleXMLElement $document1 */
        $document1 =  $archiveNode->Document[1];

        $this->assertEquals("Test docuemnt 1", (string) $document0->Description);
        $this->assertEquals("Test document 2", (string) $document1->Description);
        $this->assertEquals(
            "{{pastell:string:test1}}",
            $document0->children(RelaxNgImportAgapeAnnotation::PASTELL_ANNOTATION_NS)->annotation
        );
        $this->assertEquals(
            "{{pastell:string:test2}}",
            $document1->children(RelaxNgImportAgapeAnnotation::PASTELL_ANNOTATION_NS)->annotation
        );
        $this->assertEquals(
            "{{pastell:string:in1}}",
            $document0->Attachment->children(RelaxNgImportAgapeAnnotation::PASTELL_ANNOTATION_NS)->annotation
        );
        $this->assertEquals(
            "{{pastell:string:in2}}",
            $document1->Attachment->children(RelaxNgImportAgapeAnnotation::PASTELL_ANNOTATION_NS)->annotation
        );
    }
}
