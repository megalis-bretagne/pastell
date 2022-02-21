<?php

class ConnecteurDefinitionFilesTest extends PastellTestCase
{
    /** @var  ConnecteurDefinitionFiles */
    private $connecteurDefinitionFiles;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connecteurDefinitionFiles =
            $this->getObjectInstancier()->getInstance(ConnecteurDefinitionFiles::class);
    }

    protected function tearDown(): void
    {
        $this->setListPack(["pack_test" => true]);
    }

    public function testGetAllType()
    {
        $result = $this->connecteurDefinitionFiles->getAllType();
        $this->assertContains("mailsec", $result);
    }

    public function testGetAllTypeTwoConnecteur()
    {
        $this->getInternalAPI()->post(
            "/Extension/",
            array('path' => __DIR__ . '/../fixtures/extensions/extension-test')
        );
        $result = $this->connecteurDefinitionFiles->getAllType();
        $this->assertEquals(1, array_count_values($result)['test']);
    }

    public function testGetAllRestricted()
    {
        $this->setListPack(["pack_test" => false]);
        $result = $this->connecteurDefinitionFiles->getAllRestricted();
        $this->assertContains("test", $result);
        $result = $this->connecteurDefinitionFiles->getAllRestricted(true);
        $this->assertContains("test", $result);

        $this->setListPack(["pack_test" => true]);
        $result = $this->connecteurDefinitionFiles->getAllRestricted();
        $this->assertEmpty($result);
        $result = $this->connecteurDefinitionFiles->getAllRestricted(true);
        $this->assertEmpty($result);
    }
}
