<?php

class DocumentEmailReponseSQLTest extends PastellTestCase
{

    private function getDocumentEmailReponseSQL()
    {
        return $this->getObjectInstancier()->getInstance(DocumentEmailReponseSQL::class);
    }

    public function testGetDocumentReponseId()
    {

        $this->assertFalse($this->getDocumentEmailReponseSQL()->getDocumentReponseId(42));
    }

    public function testAddDocumentReponseId()
    {
        $this->getDocumentEmailReponseSQL()->addDocumentReponseId(42, 43);
        $this->assertEquals(43, $this->getDocumentEmailReponseSQL()->getDocumentReponseId(42));
    }

    public function testGetInfo()
    {
        $this->getDocumentEmailReponseSQL()->addDocumentReponseId(42, 43);
        $this->assertEquals(array (
            'id_reponse' => '1',
            'id_de' => '42',
            'id_d_reponse' => '43',
            'is_lu' => '0',
            'has_reponse' => '0',
        ), $this->getDocumentEmailReponseSQL()->getInfo(42));
    }

    public function testValidateReponse()
    {
        $this->getDocumentEmailReponseSQL()->addDocumentReponseId(42, 43);
        $this->assertEquals(array (
            'id_reponse' => '1',
            'id_de' => '42',
            'id_d_reponse' => '43',
            'is_lu' => '0',
            'has_reponse' => '0',
        ), $this->getDocumentEmailReponseSQL()->getInfo(42));
        $this->getDocumentEmailReponseSQL()->validateReponse(42);
        $this->assertEquals(array (
            'id_reponse' => '1',
            'id_de' => '42',
            'id_d_reponse' => '43',
            'is_lu' => '0',
            'has_reponse' => '1',
        ), $this->getDocumentEmailReponseSQL()->getInfo(42));
    }

    public function testgetInfoFromIdReponse()
    {
        $this->getDocumentEmailReponseSQL()->addDocumentReponseId(42, 43);
        $this->assertEquals(array (
            'id_reponse' => '1',
            'id_de' => '42',
            'id_d_reponse' => '43',
            'is_lu' => '0',
            'has_reponse' => '0',
        ), $this->getDocumentEmailReponseSQL()->getInfoFromIdReponse(43));
    }


    public function testSetLu()
    {
        $this->getDocumentEmailReponseSQL()->addDocumentReponseId(42, 43);
        $this->assertEquals(array (
            'id_reponse' => '1',
            'id_de' => '42',
            'id_d_reponse' => '43',
            'is_lu' => '0',
            'has_reponse' => '0',
        ), $this->getDocumentEmailReponseSQL()->getInfo(42));
        $this->getDocumentEmailReponseSQL()->setLu(43);
        $this->assertEquals(array (
            'id_reponse' => '1',
            'id_de' => '42',
            'id_d_reponse' => '43',
            'is_lu' => '1',
            'has_reponse' => '0',
        ), $this->getDocumentEmailReponseSQL()->getInfo(42));
    }

    public function testgetAllReponse()
    {

        $id_d_mailsec = "MAILSEC";
        $id_d_reponse = "REPONSE";
        $documentEmail =  $this->getObjectInstancier()->getInstance(DocumentEmail::class);

        $document = $this->getObjectInstancier()->getInstance(Document::class);
        $document->save($id_d_mailsec, "mailsec");


        $document->save($id_d_reponse, "mailsec-reponse");
        $document->setTitre($id_d_reponse, "MON TITRE");


        $key = $documentEmail->add($id_d_mailsec, "foo@bar", "to");
        $info = $documentEmail->getInfoFromKey($key);
        $this->getDocumentEmailReponseSQL()->addDocumentReponseId($info['id_de'], $id_d_reponse);
        $this->getDocumentEmailReponseSQL()->validateReponse($info['id_de']);
        $this->assertEquals(
            array (
                1 =>
                    array (
                        'id_de' => '1',
                        'id_d_reponse' => 'REPONSE',
                        'is_lu' => '0',
                        'titre' => 'MON TITRE',
                    ),
            ),
            $this->getDocumentEmailReponseSQL()->getAllReponse($id_d_mailsec)
        );
    }
}
