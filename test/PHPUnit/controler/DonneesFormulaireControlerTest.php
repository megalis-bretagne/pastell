<?php

class DonneesFormulaireControlerTest extends ControlerTestCase
{

    /**
     * @throws Exception
     */
    public function testDownloadAllAction()
    {
        $tmpFolder = new TmpFolder();
        $tmp_folder = $tmpFolder->create();
        /* ZipArchive ca marche pas avec le workspace émulé en mémoire */
        $this->getObjectInstancier()->setInstance('workspacePath', $tmp_folder);

        $info = $this->getInternalAPI()->post("entite/1/document", array('type' => 'actes-generique'));
        $id_d = $info['id_d'];
        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);
        $donneesFormulaire->addFileFromCopy(
            'autre_document_attache',
            "vide.pdf",
            __DIR__ . "/../fixtures/vide.pdf",
            0
        );
        $donneesFormulaire->addFileFromCopy(
            'autre_document_attache',
            __DIR__ . "/../fixtures/vide.pdf",
            __DIR__ . "/../fixtures/test_extract_zip_structure/7756W3_9/7756_Bordereau_versement.pdf"
        )
        ;

        /** @var DonneesFormulaireControler $documentControler */
        $documentControler = $this->getControlerInstance(DonneesFormulaireControler::class);

        $this->setGetInfo(['id_e' => 1,'id_d' => $info['id_d'],'field' => 'autre_document_attache']);

        $this->expectOutputRegex("#Content-disposition: attachment; filename=\"fichier-1-$id_d-autre_document_attache.zip\"#");
        $documentControler->downloadAllAction();
        $tmpFolder->delete($tmp_folder);
    }
}
