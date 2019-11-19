<?php

class ActionExecutorFactoryTest extends PastellTestCase {

    /** @return ActionExecutorFactory */
    private function getActionExcecutorFactory(){
        return $this->getObjectInstancier()->getInstance(ActionExecutorFactory::class);
    }

    /** @return JobManager */
    private function getJobManager(){
        return $this->getObjectInstancier()->getInstance(JobManager::class);
    }

    /** @return JobQueueSQL */
    private function getJobQueueSQL(){
        return $this->getObjectInstancier()->getInstance(JobQueueSQL::class);
    }

    /** @return WorkerSQL */
    private function getWorkerSQL(){
        return $this->getObjectInstancier()->getInstance(WorkerSQL::class);
    }

    public function testAccessOnConnecteur() {

        $id_ce = 13;

        $this->getJobManager()->setJobForConnecteur(
            $id_ce,
            "ok",
            "test"
        );

        $this->assertTrue(
            $this->getActionExcecutorFactory()->executeOnConnecteur($id_ce,0,"ok")
        );
        $this->assertEquals(
            "OK !",
            $this->getActionExcecutorFactory()->getLastMessage()
        );

    }

    public function testConcurentConnecteurAccess(){

        $id_ce = 13;

        $id_job = $this->getJobManager()->setJobForConnecteur(
            $id_ce,
            "une_action_long_auto",
            "test"
        );

        $id_worker = $this->getWorkerSQL()->create(42);
        $this->getWorkerSQL()->attachJob($id_worker,$id_job);

        $this->assertFalse(
            $this->getActionExcecutorFactory()->executeOnConnecteur(
                $id_ce,
                0,
                "une_action_long_auto")
        );
        $this->assertEquals(
            "Une action est déjà en cours de réalisation sur ce connecteur",
            $this->getActionExcecutorFactory()->getLastMessage()
        );
    }

    /**
     * @throws Exception
     */
    public function testExecuteOnDocumentWithoutConnecteur(){

        $document = $this->createDocument('pdf-generique');
        $id_d = $document['id_d'];
        $this->getInternalAPI()->patch(
            "/entite/1/document/$id_d",
            array('libelle'=>'Test pdf générique',
                'envoi_ged_1'=>'1',
            )
        );

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);
        $donneesFormulaire->addFileFromCopy(
            'document',
            'Délib Libriciel.pdf',
            __DIR__ . "/../module/pdf-generique/fixtures/Délib Libriciel.pdf"
        );

        $this->triggerActionOnDocument($id_d, 'orientation');

        $action = $this->triggerActionOnDocument($id_d, 'send-ged-1');
        $this->assertFalse($action);

        $this->assertLastMessage("Aucun connecteur de type GED n'est associé au flux pdf-generique");

        $id_job  = $this->getJobQueueSQL()->getJobIdForDocument(1,$id_d);
        $job_info = $this->getJobQueueSQL()->getJobInfo($id_job);

        $this->assertEquals(1, $job_info['is_lock']);

    }

}