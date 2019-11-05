<?php

class ExtensionsTest extends PHPUnit\Framework\TestCase
{

    private function getExtensionTestPath()
    {
        return realpath(__DIR__ . '/../fixtures/extensions/extension-test/');
    }
    
    private function getExtensionSQLMock($extensionSQLGetAllResult)
    {
        $extensionSQL = $this->getMockBuilder('ExtensionSQL')->disableOriginalConstructor()->getMock();
        $extensionSQL->expects($this->any())->method('getAll')->will($this->returnValue($extensionSQLGetAllResult));
        if (isset($extensionSQLGetAllResult[0])) {
            $extensionSQL->expects($this->any())->method('getInfo')->will($this->returnValue($extensionSQLGetAllResult[0]));
        }
        return $extensionSQL;
    }
    
    private function getManifestFactory()
    {
        return new ManifestFactory(__DIR__ . "/../fixtures/", new YMLLoader(new MemoryCacheNone()));
    }

    private function getExtensions($extensionSQLGetAllResult)
    {
        $extensionSQL = $this->getExtensionSQLMock($extensionSQLGetAllResult);
        /** @var ExtensionSQL $extensionSQL */
        return new Extensions(
            $extensionSQL,
            $this->getManifestFactory(),
            "/tmp",
            new MemoryCacheNone(),
            10,
            "/tmp/"
        );
    }
    
    private function getExtensionsTest()
    {
        $extension_test_path = $this->getExtensionTestPath();
        return $this->getExtensions(array(array('id_e' => '42','path' => $extension_test_path)));
    }
    
    public function testGetAllEmpty()
    {
        $extensions = $this->getExtensions(array());
        $this->assertEmpty($extensions->getAll());
    }

    public function testGetAll()
    {
        $extensions = $this->getExtensionsTest();
        
        $this->assertArrayHasKey(42, $extensions->getAll());
    }
    
    public function testGetAllConnecteurEmpty()
    {
        $extensions = $this->getExtensions(array());
        $this->assertEmpty($extensions->getAllConnecteur());
    }
    
    public function testGetAllConnecteur()
    {
        $extensions = $this->getExtensionsTest();
        $this->assertArrayHasKey('connecteur-test', $extensions->getAllConnecteur());
    }
    
    public function testGetConnecteurPathNotExists()
    {
        $extensions = $this->getExtensionsTest();
        $path = $extensions->getConnecteurPath('foo');
        $this->assertFalse($path);
    }
    
    public function testGetConnecteurPath()
    {
        $extensions = $this->getExtensionsTest();
        $path = $extensions->getConnecteurPath('connecteur-test');
        $this->assertEquals($this->getExtensionTestPath() . "/connecteur/connecteur-test", $path);
    }
    
    public function testGetAllModule()
    {
        $extensions = $this->getExtensionsTest();
        $this->assertArrayHasKey('module-test', $extensions->getAllModule());
    }
    
    public function testGetModulePathEmpty()
    {
        $extensions = $this->getExtensionsTest();
        $path = $extensions->getModulePath('bar');
        $this->assertFalse($path);
    }
    
    public function testGetModulePath()
    {
        $extensions = $this->getExtensionsTest();
        $path = $extensions->getModulePath('module-test');
        $this->assertEquals($this->getExtensionTestPath() . "/module/module-test", $path);
    }
    
    public function testGetInfo()
    {
        $extensions = $this->getExtensionsTest();
        $info = $extensions->getInfo(42);
        $this->assertEquals('glaneur', $info['id']);
    }

    public function testGetInfoRevisionNotOK()
    {
        $extension_test_path = $this->getExtensionTestPath();
        $extensionSQLGetAllResult = array(array('id_e' => '42','path' => $extension_test_path));

        $extensions = $this->getExtensions($extensionSQLGetAllResult);
        $info = $extensions->getInfo(42);
        $this->assertNotEmpty($info['warning']);
    }
    
    public function testGetInfoNotExists()
    {
        $extensionSQLGetAllResult = array(array('id_e' => '42','path' => 'toto'));
        $extensions = $this->getExtensions($extensionSQLGetAllResult);
        $info = $extensions->getInfo(42);
        $this->assertNotEmpty($info['error']);
    }
    
    public function testGetInfoNoManifest()
    {
        $extensionSQLGetAllResult = array(array('id_e' => '42','path' => '/tmp'));
        $extensions = $this->getExtensions($extensionSQLGetAllResult);
        $info = $extensions->getInfo(42);
        $this->assertEquals("Le fichier manifest.yml n'a pas été trouvé dans /tmp", $info['warning-detail']);
    }

    public function testLoadConnecteurType()
    {
        $extensions = $this->getExtensionsTest();
        $extensions->loadConnecteurType();
        $extension_test_path = $this->getExtensionTestPath();
        $this->assertRegExp("#$extension_test_path/connecteur-type/$#", get_include_path());
    }
}
