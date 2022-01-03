<?php

class ActionExecutorTest extends PastellTestCase
{
    /**
     * @return ActionExecutor|PHPUnit_Framework_MockObject_MockObject
     */
    private function getActionExecutor()
    {
        return $this->getMockForAbstractClass(
            ActionExecutor::class,
            [
                $this->getObjectInstancier()
            ]
        );
    }

    public function testNoConnecteur()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Aucun connecteur de type blutrepoi n\'est associé au type de dossier actes-generique');
        $concreteActionExecutor = $this->getActionExecutor();
        $concreteActionExecutor->setEntiteId(1);
        $concreteActionExecutor->setDocumentId('actes-generique', 42);
        $concreteActionExecutor->getConnecteur('blutrepoi');
    }

    private function getActionExecutorMailSec()
    {
        $concreteActionExecutor = $this->getActionExecutor();
        $concreteActionExecutor->setEntiteId(1);
        $concreteActionExecutor->setDocumentId('mailsec', 42);
        return $concreteActionExecutor;
    }

    public function testGetConnecteur()
    {
        $this->getObjectInstancier()->Document->save(42, 'mailsec');
        $concreteActionExecutor = $this->getActionExecutorMailSec();
        $connecteur = $concreteActionExecutor->getConnecteur('mailsec');
        $this->assertInstanceOf('MailSec', $connecteur);
    }

    public function testGetConnecteurConfigByType()
    {
        $this->getObjectInstancier()->Document->save(42, 'mailsec');
        $concreteActionExecutor = $this->getActionExecutorMailSec();
        $connecteur_config = $concreteActionExecutor->getConnecteurConfigByType('mailsec');
        $this->assertEquals('ne-pas-repondre@libriciel.coop', $connecteur_config->getWithDefault('mailsec_from'));
    }

    public function testGetConnecteurConfig()
    {
        $concreteActionExecutor = $this->getActionExecutorMailSec();
        $connecteur_config = $concreteActionExecutor->getConnecteurConfig(11);
        $this->assertEquals('ne-pas-repondre@libriciel.coop', $connecteur_config->getWithDefault('mailsec_from'));
    }

    public function testGetIdMappingWhenEmpty()
    {
        $concreteActionExecutor = $this->getActionExecutorMailSec();
        $this->assertEmpty($concreteActionExecutor->getIdMapping()->getAll());
    }

    public function testGetIdMapping()
    {
        $concreteActionExecutor = $this->getActionExecutorMailSec();
        $concreteActionExecutor->setDocumentId('pdf-generique', 42);
        $concreteActionExecutor->setAction('send-iparapheur');
        $this->assertEquals([
            'objet' => 'libelle',
            'iparapheur_has_date_limite' => 'has_date_limite',
            'iparapheur_date_limite' => 'date_limite',
            'autre_document_attache' => 'annexe'
        ], $concreteActionExecutor->getIdMapping()->getAll());
    }
}
