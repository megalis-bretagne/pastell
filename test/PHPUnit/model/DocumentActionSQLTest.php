<?php

class DocumentActionSQLTest extends PastellTestCase
{
    /** @var DocumentActionSQL */
    private $documentActionSQL;

    protected function setUp()
    {
        parent::setUp();
        $this->documentActionSQL = $this->getObjectInstancier()->getInstance(DocumentActionSQL::class);
    }

    /**
     * @throws Exception
     */
    public function testGetLastActionNotModif()
    {
        $id_d = $this->createDocument('test')['id_d'];
        $this->documentActionSQL->add($id_d, self::ID_E_COL, self::ID_U_ADMIN, 'action-test');
        $this->documentActionSQL->add($id_d, self::ID_E_COL, self::ID_U_ADMIN, 'modification');

        $this->assertSame(
            'action-test',
            $this->documentActionSQL->getLastActionNotModif($id_d)
        );
        $this->assertEquals(
            'modification',
            $this->documentActionSQL->getLastActionInfo($id_d, self::ID_E_COL)['action']
        );
    }
}
