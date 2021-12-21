<?php

class GradeSQLTest extends PastellTestCase
{
    /** @var GradeSQL */
    private $gradeSQL;

    protected function setUp()
    {
        parent::setUp();
        $this->gradeSQL = new GradeSQL($this->getSQLQuery());
        $info = array (
            "FS_ADMINISTR",
            "Administrative",
            "CE_ADJADM",
            "Adjoints administratifs territoriaux",
            "GR_ADM037" ,
            "Adjoint administratif territorial de 2ème classe"
        );


        $this->gradeSQL->add($info);
    }

    public function testGetAll()
    {
        $result = $this->gradeSQL->getAll();
        $this->assertEquals(
            "Adjoint administratif territorial de 2ème classe",
            $result['Administrative']['Adjoints administratifs territoriaux'][0]
        );
    }

    public function testClean()
    {
        $this->gradeSQL->clean();
        $this->assertEmpty($this->gradeSQL->getAll());
    }

    public function testGetFiliere()
    {
        $this->assertEquals("Administrative", $this->gradeSQL->getFiliere()[0]['name']);
    }

    public function testGetCadreEmploi()
    {
        $this->assertEquals(
            "Adjoints administratifs territoriaux",
            $this->gradeSQL->getCadreEmploi("Administrative")[0]['name']
        );
    }

    public function testGetLibelle()
    {
        $this->assertEquals(
            "Adjoint administratif territorial de 2ème classe",
            $this->gradeSQL->getLibelle("Administrative", "Adjoints administratifs territoriaux")[0]['name']
        );
    }
}
