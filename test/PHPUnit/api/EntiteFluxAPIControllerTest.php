<?php

class EntiteFluxAPIControllerTest extends PastellTestCase
{
    private function associateConnecteur(): array
    {
        return $this->getInternalAPI()->post('/entite/1/flux/test/connecteur/12', ['type' => 'test']);
    }

    public function testAssociateConnecteur(): void
    {
        self::assertSame(['id_fe' => '10'], $this->associateConnecteur());
    }

    public function testDoActionAction(): void
    {
        $this->associateConnecteur();
        $result = $this->getInternalAPI()->post(
            '/entite/1/flux/test/action',
            [
                'type' => 'test',
                'id_ce' => 12,
                'flux' => 'test',
                'action' => 'ok',
            ]
        );
        static::assertSame(
            [
                'result' => true,
                'message' => 'OK !',
            ],
            $result
        );
    }

    public function testDeleteFluxConnecteurAction()
    {
        $info_before = $this->getInternalAPI()->get("/entite/1/flux");
        $this->getInternalAPI()->delete("/entite/1/flux/test?id_fe=1");
        $info_after = $this->getInternalAPI()->get("/entite/1/flux");
        $this->assertCount(count($info_before) - 1, $info_after);
    }

    public function testDeleteFluxConnecteurNotExist(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Le connecteur-flux n'existe pas : {id_fe=42}");
        $this->getInternalAPI()->delete('/entite/1/flux/test?id_fe=42');
    }

    public function testDeleteFluxConnecteurNotExistForEntity(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Le connecteur-flux n'existe pas sur l'entité spécifié : {id_fe=1, id_e=2}");
        $this->getInternalAPI()->delete('/entite/2/flux/test?id_fe=1');
    }

    public function testDoActionNotExist(): void
    {
        $this->associateConnecteur();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("L'action foo n'existe pas.");
        $this->getInternalAPI()->post(
            '/entite/1/flux/test/action',
            [
                'type' => 'test',
                'id_ce' => 12,
                'flux' => 'test',
                'action' => 'foo',
            ]
        );
    }

    public function testDoActionFail(): void
    {
        $this->associateConnecteur();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Fail !');
        $this->getInternalAPI()->post(
            '/entite/1/flux/test/action',
            [
                'type' => 'test',
                'id_ce' => 12,
                'flux' => 'test',
                'action' => 'fail',
            ]
        );
    }

    public function testDoActionNotPossible(): void
    {
        $this->associateConnecteur();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("L'action « not_possible »  n'est pas permise : internal-action n'est pas vérifiée");
        $this->getInternalAPI()->post(
            '/entite/1/flux/test/action',
            [
                'type' => 'test',
                'id_ce' => 12,
                'flux' => 'test',
                'action' => 'not_possible',
            ]
        );
    }

    public function testDoActionNoConnecteur(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Le connecteur de type SAE n'existe pas pour le flux test.");
        $this->getInternalAPI()->post(
            '/entite/1/flux/test/action',
            [
                'type' => 'SAE',
                'id_ce' => 12,
                'flux' => 'test',
                'action' => 'ok',
            ]
        );
    }

    public function testDoPostTwoSameType(): void
    {
        $connecteur_sae = $this->createConnector('as@lae-rest', 'TEST SAE');
        $this->associateFluxWithConnector($connecteur_sae['id_ce'], 'test', 'SAE');
        $this->associateFluxWithConnector(12, 'test', 'test');
        $connecteur_2 = $this->createConnector('test', 'TEST 2');
        $this->associateFluxWithConnector($connecteur_2['id_ce'], 'test', 'test', PastellTestCase::ID_E_COL, 1);

        $result = $this->getInternalAPI()->get('/entite/1/flux', ['flux' => 'test']);
        self::assertSame(
            [
                [
                    'id_fe' => '10',
                    'id_e' => '1',
                    'flux' => 'test',
                    'id_ce' => '14',
                    'type' => 'SAE',
                    'num_same_type' => 0,
                ],
                [
                    'id_fe' => '11',
                    'id_e' => '1',
                    'flux' => 'test',
                    'id_ce' => '12',
                    'type' => 'test',
                    'num_same_type' => 0,
                ],
                [
                    'id_fe' => '12',
                    'id_e' => '1',
                    'flux' => 'test',
                    'id_ce' => '15',
                    'type' => 'test',
                    'num_same_type' => 1,
                ],
            ],
            $result
        );
    }
}
