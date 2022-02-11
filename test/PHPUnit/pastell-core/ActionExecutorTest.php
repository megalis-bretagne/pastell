<?php

class ActionExecutorTest extends PastellTestCase
{
    /**
     * @return ActionExecutor|PHPUnit_Framework_MockObject_MockObject
     * @throws ReflectionException
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

    /**
     * @throws ReflectionException
     * @throws UnrecoverableException
     * @throws NotFoundException
     */
    public function testNoConnecteur()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage(
            'Aucun connecteur de type blutrepoi n\'est associé au type de dossier actes-generique'
        );
        $concreteActionExecutor = $this->getActionExecutor();
        $concreteActionExecutor->setEntiteId(1);
        $concreteActionExecutor->setDocumentId('actes-generique', 42);
        $concreteActionExecutor->getConnecteur('blutrepoi');
    }

    /**
     * @throws ReflectionException
     */
    private function getActionExecutorMailSec()
    {
        $concreteActionExecutor = $this->getActionExecutor();
        $concreteActionExecutor->setEntiteId(1);
        $concreteActionExecutor->setDocumentId('mailsec', 42);
        return $concreteActionExecutor;
    }

    /**
     * @throws ReflectionException
     * @throws UnrecoverableException
     * @throws NotFoundException
     */
    public function testGetConnecteur()
    {
        $this->getObjectInstancier()->getInstance(DocumentSQL::class)->save(42, 'mailsec');
        $concreteActionExecutor = $this->getActionExecutorMailSec();
        $connecteur = $concreteActionExecutor->getConnecteur('mailsec');
        $this->assertInstanceOf('MailSec', $connecteur);
    }

    /**
     * @throws ReflectionException
     * @throws Exception
     */
    public function testGetConnecteurConfigByType()
    {
        $this->getObjectInstancier()->getInstance(DocumentSQL::class)->save(42, 'mailsec');
        $concreteActionExecutor = $this->getActionExecutorMailSec();
        $connecteur_config = $concreteActionExecutor->getConnecteurConfigByType('mailsec');
        $this->assertEquals('PASTELL', $connecteur_config->getWithDefault('mailsec_from_description'));
    }

    /**
     * @throws ReflectionException
     */
    public function testGetConnecteurConfig()
    {
        $concreteActionExecutor = $this->getActionExecutorMailSec();
        $connecteur_config = $concreteActionExecutor->getConnecteurConfig(11);
        $this->assertEquals('PASTELL', $connecteur_config->getWithDefault('mailsec_from_description'));
    }

    /**
     * @throws ReflectionException
     */
    public function testGetIdMappingWhenEmpty()
    {
        $concreteActionExecutor = $this->getActionExecutorMailSec();
        $this->assertEmpty($concreteActionExecutor->getIdMapping()->getAll());
    }

    /**
     * @throws ReflectionException
     */
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
