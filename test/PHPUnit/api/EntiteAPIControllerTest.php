<?php

class EntiteAPIControllerTest extends PastellTestCase
{
    public function testList()
    {
        $list = $this->getInternalAPI()->get("/entite");
        $this->assertEquals('Bourg-en-Bresse', $list[0]['denomination']);
    }

    public function testCreate()
    {
        $result = $this->getInternalAPI()->post(
            "/entite",
            array(
                    'denomination' => 'Métropolis',
                    'type' => 'collectivite',
                    'siren' => '677203002'
            )
        );
        $this->assertNotEmpty($result['id_e']);
    }

    public function testDelete()
    {
        $this->getInternalAPI()->delete("/entite/2");
        $this->expectException("NotFoundException");
        $this->expectExceptionMessage("L'entité 2 n'a pas été trouvée");
        $this->getInternalAPI()->get("/entite/2");
    }

    public function testEdit()
    {
        $info = $this->getInternalAPI()->patch(
            "/entite/1",
            array('denomination' => 'Mâcon','siren' => '677203002')
        );
        $this->assertEquals('Mâcon', $info['denomination']);
    }

    public function testDetail()
    {
        $info = $this->getInternalAPI()->get("/entite/1");
        $this->assertEquals('Bourg-en-Bresse', $info['denomination']);
    }

    public function testCreateWithEditAction()
    {
        $info = $this->getInternalAPI()->patch(
            "/entite/1",
            array(
                'denomination' => 'Métropolis',
                'type' => 'collectivite',
                'siren' => '677203002',
                'create' => true
            )
        );
        $this->assertNotEmpty($info['id_e']);
        $this->assertNotEquals(1, $info['id_e']);
    }

    public function testCreateFilleCDG()
    {
        $info = $this->getInternalAPI()->patch(
            "/entite/1",
            array(
                'id_e' => 2,
                'denomination' => 'Métropolis',
                'type' => 'collectivite',
                'siren' => '677203002',
                'centre_de_gestion' => 1,
            )
        );
        $this->assertEquals(1, $info['centre_de_gestion']);
    }

    public function testCreateFille()
    {
        $info = $this->getInternalAPI()->patch(
            "/entite/2",
            array(
                'denomination' => 'Métropolis',
                'type' => 'collectivite',
                'siren' => '677203002',
                'entite_mere' => 1,
            )
        );
        $this->assertEquals(1, $info['entite_mere']);
    }

    public function testCreateWithoutName()
    {
        $this->expectException("Exception");
        $this->expectExceptionMessage("Le nom (denomination) est obligatoire");
        $this->getInternalAPI()->post("/entite");
    }

    public function testCreateWithoutType()
    {
        $this->expectException("Exception");
        $this->expectExceptionMessage("Le type d'entité doit être renseigné");
        $this->getInternalAPI()->post("/entite", array("denomination" => "toto"));
    }

    public function testCreateWithoutSiren()
    {
        $this->expectException("Exception");
        $this->expectExceptionMessage("Le siren est obligatoire");
        $this->getInternalAPI()->post("/entite", array("denomination" => "toto",'type' => Entite::TYPE_COLLECTIVITE));
    }

    public function testCreateBadSiren()
    {
        $this->expectException("Exception");
        $this->expectExceptionMessage("Le siren « 123456789 » ne semble pas valide");
        $this->getInternalAPI()->post(
            "/entite",
            array(
                "denomination" => "toto",
                'type' => Entite::TYPE_COLLECTIVITE,
                'siren' => '123456789'
            )
        );
    }

    public function testCreateServiceInRootEntite()
    {
        $this->expectException("Exception");
        $this->expectExceptionMessage("Un service doit être ataché à une entité mère");
        $this->getInternalAPI()->post(
            "/entite",
            array(
                "denomination" => "toto",
                'type' => Entite::TYPE_SERVICE,
                'siren' => '123456789'
            )
        );
    }
}
