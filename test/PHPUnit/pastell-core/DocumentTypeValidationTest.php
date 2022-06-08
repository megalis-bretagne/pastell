<?php

class DocumentTypeValidationTest extends PHPUnit\Framework\TestCase
{
    /** @var  DocumentTypeValidation */
    private $documentTypeValidation;


    protected function setUp()
    {
        $this->documentTypeValidation = new DocumentTypeValidation(new YMLLoader(new MemoryCacheNone()));
        $this->documentTypeValidation->setListPack(["pack_chorus_pro" => false, "pack_marche" => false]);
        $this->documentTypeValidation->setConnecteurTypeList(array('mailsec'));
        $this->documentTypeValidation->setActionClassList(array('Supprimer', 'StandardAction', 'Defaut'));
        $this->documentTypeValidation->setEntiteTypeList(array());
        $this->documentTypeValidation->setConnecteurTypeActionClassList(
            ['MailsecEnvoyer', 'MailsecRenvoyer', 'MailsecComputeReadMail', 'MailsecNotReceived']
        );
    }

    public function testValidate()
    {
        $result = $this->documentTypeValidation->validate(PASTELL_PATH . "/module/mailsec/definition.yml");
        //print_r($this->documentTypeValidation->getLastError());
        $this->assertTrue($result);
    }

    public function testGetModuleDefinition()
    {
        $this->assertNotEmpty($this->documentTypeValidation->getModuleDefinition());
    }

    public function testGetLastError()
    {
        $this->assertFalse($this->documentTypeValidation->validate(""));
        $this->assertEquals("Fichier definition.yml absent", $this->documentTypeValidation->getLastError()[0]);
    }

    public function testConnecteurType()
    {
        $this->documentTypeValidation->setConnecteurTypeList(array("signature"));
        $this->documentTypeValidation->setConnecteurTypeActionClassList(array("SignatureEnvoie"));
        $this->assertTrue($this->documentTypeValidation->validate(__DIR__ . "/fixtures/definition-with-connecteur-type.yml"));
    }

    public function testConnecteurTypeAbsent()
    {
        $this->assertFalse($this->documentTypeValidation->validate(__DIR__ . "/fixtures/definition-with-connecteur-type.yml"));
        $this->assertEquals("action:<b>test</b>:connecteur-type:<b>signature</b> n'est pas un connecteur du système", $this->documentTypeValidation->getLastError()[1]);
        $this->assertEquals("action:<b>test</b>:connecteur-type-action:<b>SignatureEnvoie</b> n'est pas une classe d'action du système", $this->documentTypeValidation->getLastError()[2]);
    }

    public function testConnecteurTypeMappingFailed()
    {
        $this->documentTypeValidation->setConnecteurTypeList(array("signature"));
        $this->documentTypeValidation->setConnecteurTypeActionClassList(array("SignatureEnvoie"));
        $this->assertFalse($this->documentTypeValidation->validate(__DIR__ . "/fixtures/definition-with-connecteur-type-failed.yml"));
        $this->assertEquals("action:<b>test</b>:connecteur-type-mapping:document:<b>toto</b> n'est pas un élément du formulaire", $this->documentTypeValidation->getLastError()[0]);
    }

    public function testModifiationNoChangeEtat()
    {
        $this->assertTrue($this->documentTypeValidation->validate(__DIR__ . "/fixtures/definition-with-modification-no-change-etat.yml"));
    }

    public function testRestrictionPack()
    {
        $this->assertTrue($this->documentTypeValidation->validate(__DIR__ . "/fixtures/definition-with-restriction-pack.yml"));
    }

    public function testRestrictionPackAbsent()
    {
        $this->assertFalse($this->documentTypeValidation->validate(__DIR__ . "/fixtures/definition-with-wrong_restriction-pack.yml"));
        $this->assertEquals("restriction_pack:<b>pack_wrong_pack</b> n'est pas défini dans la liste des packs", $this->documentTypeValidation->getLastError()[0]);
    }
}
