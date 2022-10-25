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
            [
                    'denomination' => 'Métropolis',
                    'type' => 'collectivite',
                    'siren' => '677203002'
            ]
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
            ['denomination' => 'Mâcon','siren' => '677203002']
        );
        $this->assertEquals('Mâcon', $info['denomination']);
    }

    public function testDetail()
    {
        $info = $this->getInternalAPI()->get("/entite/1");
        $this->assertEquals('Bourg-en-Bresse', $info['denomination']);
    }

    public function testCreateFilleCDG()
    {
        $info = $this->getInternalAPI()->patch(
            "/entite/1",
            [
                'id_e' => 2,
                'denomination' => 'Métropolis',
                'type' => 'collectivite',
                'siren' => '677203002',
                'centre_de_gestion' => 1,
            ]
        );
        $this->assertEquals(1, $info['centre_de_gestion']);
    }

    public function testCreateFille()
    {
        $info = $this->getInternalAPI()->patch(
            "/entite/2",
            [
                'denomination' => 'Métropolis',
                'type' => 'collectivite',
                'siren' => '677203002',
                'entite_mere' => 1,
            ]
        );
        $this->assertEquals(1, $info['entite_mere']);
    }

    public function testCreateWithoutName()
    {
        $this->expectException("Exception");
        $this->expectExceptionMessage("Le nom (denomination) est obligatoire");
        $this->getInternalAPI()->post("/entite");
    }

    public function testCreateWithoutSiren()
    {
        $info = $this->getInternalAPI()->post(
            "/entite",
            [
                "denomination" => "toto",
                'type' => EntiteSQL::TYPE_COLLECTIVITE
            ]
        );
        $this->assertSame("", $info['siren']);
    }

    public function testCreateBadSiren()
    {
        $this->expectException("Exception");
        $this->expectExceptionMessage("Le siren « 123456789 » ne semble pas valide");
        $this->getInternalAPI()->post(
            "/entite",
            [
                "denomination" => "toto",
                'type' => EntiteSQL::TYPE_COLLECTIVITE,
                'siren' => '123456789'
            ]
        );
    }

    public function testActivateDeactivate(): void
    {
        $entiteActivated = $this->getInternalAPI()->get('/entite/1');
        $entiteDeactivated = $this->getInternalAPI()->post('/entite/1/deactivate');
        $this->assertNotEquals($entiteActivated, $entiteDeactivated);

        $entiteReactivated = $this->getInternalAPI()->post('/entite/1/activate');
        $this->assertEquals($entiteActivated, $entiteReactivated);
    }

    public function testDeActivateFailDroit(): void
    {
        $this->getObjectInstancier()->getInstance(UtilisateurCreator::class)
            ->create('tester', 'tester', 'tester', 'tester@mail');
        $this->getObjectInstancier()->getInstance(RoleSQL::class)
            ->edit('entite:lecture', 'entiteLectureEdition');
        $this->getObjectInstancier()->getInstance(RoleSQL::class)
            ->edit('entite:edition', 'entiteLectureEdition');
        $this->getObjectInstancier()->getInstance(RoleUtilisateur::class)
            ->addRole('3', 'entiteLectureEdition', '1');
        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage('Acces interdit id_e=1, droit=entite:edition,id_u=3');
        $this->getInternalAPIAsUser('3')->post('/entite/1/deactivate');
    }
}
