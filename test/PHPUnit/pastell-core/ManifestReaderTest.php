<?php

class ManifestReaderTest extends PHPUnit\Framework\TestCase
{
    private function getManifestReader(): ManifestReader
    {
        $manifest = [
            'nom' => 'Glaneur',
            'id' => 'glaneur',
            'revision' => '$Rev: 9 $',
            'version' => 4,
            'last_changed_date' => '$LastChangedDate: 2015-08-12 11:02:27 +0200 (mer., 12 août 2015) $',
            'extensions_versions_accepted' => [2, 3],
            'extension_needed' => ['pastell-megalis' => ['version' => 1], 'pastell-mnesys' => ['version' => 2]]
        ];
        return new ManifestReader($manifest);
    }

    public function testGetInfo()
    {
        $info = $this->getManifestReader()->getInfo();
        $this->assertIsArray($info);
        $this->assertArrayHasKey('nom', $info);
        $this->assertEquals('Glaneur', $info['nom']);
    }

    public function testGetRevision()
    {
        $revision = $this->getManifestReader()->getRevision();
        $this->assertEquals('9', $revision);
    }

    public function testGetVersion()
    {
        $version = $this->getManifestReader()->getVersion();
        $this->assertEquals('4', $version);
    }

    public function testRevisionOk()
    {
        $this->assertTrue($this->getManifestReader()->isVersionOK('4'));
    }

    public function testRevisionFailed()
    {
        $this->assertFalse($this->getManifestReader()->isVersionOK('12'));
    }

    public function testGetExtensionNeeded()
    {
        $manifestReader = $this->getManifestReader();
        $extension_needed = $manifestReader->getExtensionNeeded();
        $this->assertIsArray($extension_needed);
        $this->assertArrayHasKey('pastell-mnesys', $extension_needed);
        $this->assertArrayHasKey('version', $extension_needed['pastell-mnesys']);
        $this->assertEquals(2, $extension_needed['pastell-mnesys']['version']);
    }

    public function testManifestEmpy()
    {
        $manifestReader = new ManifestReader([]);
        $this->assertFalse($manifestReader->isVersionOK('12'));
    }

    public function testGetId()
    {
        $this->assertEquals('glaneur', $this->getManifestReader()->getId());
    }

    public function testGetLastChangedDate()
    {
        $this->assertEquals('$LastChangedDate: 2015-08-12 11:02:27 +0200 (mer., 12 août 2015) $', $this->getManifestReader()->getLastChangedDate());
    }
}
