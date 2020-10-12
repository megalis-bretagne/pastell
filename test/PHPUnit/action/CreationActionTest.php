<?php

class CreationActionTest extends PastellTestCase
{

    public function testGo()
    {
        $result = $this->createDocument('test');
        $this->assertEquals('test', $result['info']['type']);
        $this->assertEquals(
            [
                'test2' => 'foo',
                'date_indexed' => date("Y-m-d"),
                'ma_checkbox' => 'true',
                'test_default' => 'Ceci est un texte mis par défaut',
                'date_in_the_past' => date("Y-m-d", strtotime('-30days')),
                'test_default_onglet_2' => 'Ceci est un autre texte de défaut'
            ],
            $result['data']
        );
    }
}
