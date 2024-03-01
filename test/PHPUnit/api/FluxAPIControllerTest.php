<?php

class FluxAPIControllerTest extends PastellTestCase
{
    public function testListAction(): void
    {
        $list = $this->getInternalAPI()->get('/flux');
        static::assertSame(
            [
                'type' => 'Mails sécurisés',
                'nom' => 'Mail sécurisé - déprécié',
            ],
            $list['mailsec']
        );
    }

    public function testInfoAction(): void
    {
        $info = $this->getInternalAPI()->get('/flux/test');
        static::assertSame(
            [
                'type' => 'url',
                'link_name' => 'toto',
                'index' => true,
                'name' => 'test1',
            ],
            $info['test1']
        );
    }

    public function testActionList(): void
    {
        $info = $this->getInternalAPI()->get('/flux/test/action');
        static::assertSame(
            [
                'rule' => [
                    'droit_id_u' => 'test:teletransmettre',
                    'content' => [
                        'test1' => 'toto',
                    ],
                    'or_1' => [
                        'last-action' => [
                            'creation',
                            'modification',
                        ],
                        'content' => [
                            'test1' => true,
                        ],
                    ],
                    'or_2' => [
                        'and_1' => [
                            'last-action' => [
                                'creation',
                                'modification',
                            ],
                            'content' => [
                                'test2' => true,
                            ],
                        ],
                        'and_2' => [
                            'last-action' => [
                                0 => 'creation'
                            ],
                        ],
                    ],
                ],
                'action-class' => 'Test',
            ],
            $info['test']
        );
    }

    public function testInfoActionNotExists(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage("Le flux foo n'existe pas sur cette plateforme");
        $this->getInternalAPI()->get('/flux/foo');
    }

    public function testListActionNotExists(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage("Le flux foo n'existe pas sur cette plateforme");
        $this->getInternalAPI()->get('/flux/foo/action');
    }

    public function testV1()
    {
        $this->expectOutputRegex("#mailsec#");
        $this->getV1("document-type.php");
    }

    public function testV1Detail()
    {
        $this->expectOutputRegex("#Destinataire#");
        $this->getV1("document-type-info.php?type=mailsec");
    }

    public function testV1Action()
    {
        $this->expectOutputRegex("#reception-partielle#");
        $this->getV1("document-type-action.php?type=mailsec");
    }
}
