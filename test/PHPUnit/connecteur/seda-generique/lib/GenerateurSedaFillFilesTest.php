<?php

require_once __DIR__ . "/../../../../../connecteur/generateur-seda/lib/GenerateurSedaFillFiles.class.php";

use PHPUnit\Framework\TestCase;

class GenerateurSedaFillFilesTest extends TestCase
{

    private const FIXTURE_FILEPATH = __DIR__ . "/../fixtures/fill-files.xml";

    private $generateurSedaFillFiles;

    public function setUp()
    {
        $this->generateurSedaFillFiles = new GenerateurSedaFillFiles(
            file_get_contents(self::FIXTURE_FILEPATH)
        );
    }

    private function getXMLFromGenerateur(): SimpleXMLElement
    {
        return simplexml_load_string($this->generateurSedaFillFiles->getXML());
    }

    public function testAddArchiveUnit()
    {
        $generateurSedaFillFiles = new GenerateurSedaFillFiles("");
        $generateurSedaFillFiles->addArchiveUnit("", "foo");
        $xml = simplexml_load_string($generateurSedaFillFiles->getXML());
        $this->assertEquals("foo", $xml->ArchiveUnit['description']);
    }

    private function getFromFixtures()
    {
        return new GenerateurSedaFillFiles(file_get_contents(__DIR__ . "/../fixtures/fill-files.xml"));
    }

    public function testAddArchiveUnitInArchiveUnit()
    {
        $this->generateurSedaFillFiles->addArchiveUnit("b463da2d-08ca-4fb9-b787-2951ad5de015", "foo");
        $this->assertEquals("foo", $this->getXMLFromGenerateur()->ArchiveUnit->ArchiveUnit['description']);
    }

    public function testDelete()
    {
        $this->generateurSedaFillFiles->deleteNode("d539cccd-00c2-4a21-be54-e76aecfa2482");
        $this->assertEquals(2, count($this->getXMLFromGenerateur()->ArchiveUnit->File));
    }

    public function testSetInfo()
    {
        $this->generateurSedaFillFiles->setNodeInfo("b463da2d-08ca-4fb9-b787-2951ad5de015", "foo", "bar");
        $this->assertEquals("foo", $this->getXMLFromGenerateur()->ArchiveUnit['description']);
    }

    public function testAddFile()
    {
        $this->generateurSedaFillFiles->addFile("b463da2d-08ca-4fb9-b787-2951ad5de015", "foo");
        $this->assertEquals("foo", $this->getXMLFromGenerateur()->ArchiveUnit->File[3]['description']);
    }

    public function testNodeNotFound()
    {
        $this->expectException(UnrecoverableException::class);
        $this->expectExceptionMessage("Node aaaa not found !?");
        $this->generateurSedaFillFiles->setNodeInfo("aaaa", "foo", "bar");
    }

    public function testSetNodeDescription()
    {
        $this->generateurSedaFillFiles->setNodeDescription("d539cccd-00c2-4a21-be54-e76aecfa2482", "foo");
        $this->assertEquals("foo", $this->getXMLFromGenerateur()->ArchiveUnit->File[0]['description']);
    }

    public function testSetNodeExpression()
    {
        $this->generateurSedaFillFiles->setNodeExpression("d539cccd-00c2-4a21-be54-e76aecfa2482", "bar");
        $this->assertEquals("bar", $this->getXMLFromGenerateur()->ArchiveUnit->File[0]['field_expression']);
    }

    public function testGetFiles()
    {
        $this->assertEquals(1, count($this->generateurSedaFillFiles->getFiles()));
        $this->assertEquals(
            3,
            count($this->generateurSedaFillFiles->getFiles("b463da2d-08ca-4fb9-b787-2951ad5de015"))
        );
    }

    public function testGetArchiveUnit()
    {
        $this->assertEquals(2, count($this->generateurSedaFillFiles->getArchiveUnit()));
        $this->assertEquals(
            1,
            count($this->generateurSedaFillFiles->getArchiveUnit("bede30e3-3a93-4aa3-9f66-dbb516edc676"))
        );
    }

    public function testCountNode()
    {
        $this->assertEquals(3, $this->generateurSedaFillFiles->countChildNode("b463da2d-08ca-4fb9-b787-2951ad5de015"));
    }

    public function testGetDescription()
    {
        $this->assertEquals(
            "Accusé de reception du controle de légalité",
            $this->generateurSedaFillFiles->getDescription("zzzzz")
        );
    }

    public function testGetParent()
    {
        $this->assertEquals(
            "Journal",
            strval($this->generateurSedaFillFiles->getParent("8fdf7e5a-1423-4b76-ae7b-7eb179653421")[1]['description'])
        );
    }

    public function testUpNode()
    {
        $this->generateurSedaFillFiles->upNode("7f0b47df-294b-4a1e-afed-1d31c96c234c");
        $this->assertEquals(
            'd539cccd-00c2-4a21-be54-e76aecfa2482',
            $this->getXMLFromGenerateur()->ArchiveUnit[0]->File[0]['id']
        );
        $this->assertEquals(
            '7f0b47df-294b-4a1e-afed-1d31c96c234c',
            $this->getXMLFromGenerateur()->ArchiveUnit[0]->File[1]['id']
        );
        $this->assertEquals(
            '3eeb84e2-2f03-4f39-929b-2e7b83d631a3',
            $this->getXMLFromGenerateur()->ArchiveUnit[0]->File[2]['id']
        );
    }

    public function testUpNodeWhenNoPreviousSibling()
    {
        $this->generateurSedaFillFiles->upNode("d539cccd-00c2-4a21-be54-e76aecfa2482");
        $this->assertXmlStringEqualsXmlFile(
            self::FIXTURE_FILEPATH,
            $this->getXMLFromGenerateur()->asXML()
        );
    }

    public function testDownNode()
    {
        $this->generateurSedaFillFiles->downNode("3eeb84e2-2f03-4f39-929b-2e7b83d631a3");

        $this->assertEquals(
            'd539cccd-00c2-4a21-be54-e76aecfa2482',
            $this->getXMLFromGenerateur()->ArchiveUnit[0]->File[0]['id']
        );
        $this->assertEquals(
            '7f0b47df-294b-4a1e-afed-1d31c96c234c',
            $this->getXMLFromGenerateur()->ArchiveUnit[0]->File[1]['id']
        );
        $this->assertEquals(
            '3eeb84e2-2f03-4f39-929b-2e7b83d631a3',
            $this->getXMLFromGenerateur()->ArchiveUnit[0]->File[2]['id']
        );
    }

    public function testUpNodeWhenNoFollowingSibling()
    {
        $this->generateurSedaFillFiles->downNode("7f0b47df-294b-4a1e-afed-1d31c96c234c");
        $this->assertXmlStringEqualsXmlFile(
            self::FIXTURE_FILEPATH,
            $this->getXMLFromGenerateur()->asXML()
        );
    }
}
