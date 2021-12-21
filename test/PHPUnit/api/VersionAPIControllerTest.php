<?php

class VersionAPIControllerTest extends PastellTestCase
{
    public function testGet()
    {
        $info = $this->getInternalAPI()->get("version");
        $this->assertEquals('1.4-fixtures', $info['version']);
    }

    public function testV1()
    {
        $this->expectOutputRegex("#1.4-fixtures#");
        $this->getV1("version.php");
    }
}
