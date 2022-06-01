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
            'date_reponse' => '1970-01-01 00:00:00',
            'has_date_reponse' => '0'
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
            'date_reponse' => '1970-01-01 00:00:00',
            'has_date_reponse' => '0'
        ), $this->getDocumentEmailReponseSQL()->getInfo(42));
        $this->getDocumentEmailReponseSQL()->validateReponse(42);
        $this->assertEquals(1, $this->getDocumentEmailReponseSQL()->getInfo(42)['has_reponse']);
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
            'date_reponse' => '1970-01-01 00:00:00',
            'has_date_reponse' => '0'
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
            'date_reponse' => '1970-01-01 00:00:00',
            'has_date_reponse' => '0'
        ), $this->getDocumentEmailReponseSQL()->getInfo(42));
        $this->getDocumentEmailReponseSQL()->setLu(43);
        $this->assertEquals(array (
            'id_reponse' => '1',
            'id_de' => '42',
            'id_d_reponse' => '43',
            'is_lu' => '1',
            'has_reponse' => '0',
            'date_reponse' => '1970-01-01 00:00:00',
            'has_date_reponse' => '0'
        ), $this->getDocumentEmailReponseSQL()->getInfo(42));
    }

    public function testgetAllReponse()
    {

        $id_d_mailsec = "MAILSEC";
        $id_d_reponse = "REPONSE";
        $documentEmail =  $this->getObjectInstancier()->getInstance(DocumentEmail::class);

        $document = $this->getObjectInstancier()->getInstance(DocumentSQL::class);
        $document->save($id_d_mailsec, "mailsec");


        $document->save($id_d_reponse, "mailsec-reponse");
        $document->setTitre($id_d_reponse, "MON TITRE");


        $key = $documentEmail->add($id_d_mailsec, "foo@bar", "to");
        $info = $documentEmail->getInfoFromKey($key);
        $this->getDocumentEmailReponseSQL()->addDocumentReponseId($info['id_de'], $id_d_reponse);
        $this->getDocumentEmailReponseSQL()->validateReponse($info['id_de']);
        $this->assertEquals(
            0,
            $this->getDocumentEmailReponseSQL()->getAllReponse($id_d_mailsec)[1]['is_lu']
        );

        $this->assertSame(
            1,
            $this->getDocumentEmailReponseSQL()->getNumberOfAnsweredMail($id_d_mailsec)
        );
        $key = $documentEmail->add($id_d_mailsec, "foo2@bar", "to");
        $info = $documentEmail->getInfoFromKey($key);
        $this->getDocumentEmailReponseSQL()->addDocumentReponseId($info['id_de'], $id_d_reponse);
        $this->getDocumentEmailReponseSQL()->validateReponse($info['id_de']);

        $this->assertSame(
            2,
            $this->getDocumentEmailReponseSQL()->getNumberOfAnsweredMail($id_d_mailsec)
        );
    }
}
