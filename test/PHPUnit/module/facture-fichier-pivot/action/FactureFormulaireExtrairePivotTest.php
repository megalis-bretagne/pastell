<?php

class FactureFormulaireExtrairePivotTest extends ExtensionCppTestCase
{
    /**
     * @throws NotFoundException
     */
    public function testExtrairePivotIsEditable()
    {

        $tmpFolder = $this->getMockBuilder('TmpFolder')
            ->disableOriginalConstructor()
            ->getMock();
        $tmpFolder->expects($this->any())->method('create')->willReturn(self::TMP_EXTRACTED);
        $tmpFolder->expects($this->any())->method('delete')->willReturn(true);
        $this->getObjectInstancier()->setInstance(TmpFolder::class, $tmpFolder);


        $zip = $this->getMockBuilder(ZipArchive::class)
            ->disableOriginalConstructor()
            ->getMock();
        $zip->expects($this->any())->method('open')->willReturn(true);
        $zip->expects($this->any())->method('extractTo')->willReturn(true);
        $zip->expects($this->any())->method('close')->willReturn(true);
        $this->getObjectInstancier()->setInstance(ZipArchive::class, $zip);

        $document = $this->createDocument("facture-formulaire-pivot");

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($document['id_d']);
        $donneesFormulaire->addFileFromCopy('fichier_facture', 'facture-pivot.xml', self::FICHIER_PIVOT);

        $result = $this->triggerActionOnDocument($document['id_d'], 'extraire-pivot');

        $this->assertTrue($result);
        $this->assertLastMessage('Le formulaire a été renseigné d\'après le fichier CPPFacturePivot');

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($document['id_d']);

        $this->assertEquals("FAC19-2512", $donneesFormulaire->get('no_facture'));
        $this->assertTrue($donneesFormulaire->isEditable('no_facture'));
    }
}
