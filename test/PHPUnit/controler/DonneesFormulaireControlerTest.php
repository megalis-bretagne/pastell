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

        $info = $this->createDocument('actes-generique');
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
        );

        /** @var DonneesFormulaireControler $documentControler */
        $documentControler = $this->getControlerInstance(DonneesFormulaireControler::class);

        $this->setGetInfo(['id_e' => 1,'id_d' => $info['id_d'],'field' => 'autre_document_attache']);

        $this->expectOutputRegex("#Content-disposition: attachment; filename\*=UTF-8''fichier-1-$id_d-autre_document_attache.zip; filename=fichier-1-$id_d-autre_document_attache.zip#");
        $documentControler->downloadAllAction();
        $tmpFolder->delete($tmp_folder);
    }

    public function testVisionneuseActionWithDroitLecture()
    {
        $id_d = $this->createDocument("helios-generique")['id_d'];
        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);
        $donneesFormulaire->addFileFromCopy(
            'fichier_reponse',
            'pes_acquit.xml',
            __DIR__ . "/../module/helios-generique/fixtures/pes_acquit_no_ack.xml"
        );

        $roleSql = $this->getObjectInstancier()->getInstance(RoleSQL::class);
        $roleSql->edit("my_role", "my_role");
        $roleSql->addDroit("my_role", "helios-generique:lecture");
        $userId = $this->getObjectInstancier()->getInstance(UtilisateurCreator::class)
            ->create('foo', 'test', 'test', 'foo@example.com');
        $this->getObjectInstancier()->getInstance(RoleUtilisateur::class)->addRole($userId, "my_role", self::ID_E_COL);

        $authentification = $this->getObjectInstancier()->getInstance(Authentification::class);
        $authentification->connexion('foo', $userId);

        /** @var DonneesFormulaireControler $donneesFormulaireControler */
        $donneesFormulaireControler = $this->getControlerInstance(DonneesFormulaireControler::class);

        try {
            $this->expectOutputRegex("#Rapport acquittement#");
            $this->setGetInfo(['id_e' => 1,'id_d' => $id_d,'field' => 'fichier_reponse']);
            $donneesFormulaireControler->visionneuseAction();
        } catch (Exception $e) {
        }
    }
}
