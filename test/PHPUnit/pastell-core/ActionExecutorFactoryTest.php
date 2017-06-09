<?php

class ActionExecutorFactoryTest extends PastellTestCase {

    /** @return ActionExecutorFactory */
    private function getActionExcecutorFactory(){
        return $this->getObjectInstancier()->getInstance("ActionExecutorFactory");
    }

    /** @return JobManager */
    private function getJobManager(){
        return $this->getObjectInstancier()->getInstance("JobManager");
    }

    /** @return WorkerSQL */
    private function getWorkerSQL(){
        return $this->getObjectInstancier()->getInstance("WorkerSQL");
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

}