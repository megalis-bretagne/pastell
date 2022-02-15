<?php

use PHPUnit\Framework\TestCase;

class GenerateurSedaFillFilesTest extends TestCase
{
    private const FIXTURE_FILEPATH = __DIR__ . "/../fixtures/fill-files.xml";

    private $generateurSedaFillFiles;

    protected function setUp()
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

    public function testAddArchiveUnitInArchiveUnit()
    {
        $this->generateurSedaFillFiles->addArchiveUnit("b463da2d-08ca-4fb9-b787-2951ad5de015", "foo");
        $this->assertEquals("foo", $this->getXMLFromGenerateur()->ArchiveUnit->ArchiveUnit['description']);
    }

    public function testDelete()
    {
        $this->generateurSedaFillFiles->deleteNode("d539cccd-00c2-4a21-be54-e76aecfa2482");
        $this->assertCount(2, $this->getXMLFromGenerateur()->ArchiveUnit->File);
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

    public function testSetNodeDoNotPutMineType()
    {
        $this->generateurSedaFillFiles->setNodeDoNotPutMineType("d539cccd-00c2-4a21-be54-e76aecfa2482", true);
        $this->assertEquals("1", $this->getXMLFromGenerateur()->ArchiveUnit->File[0]['do_not_put_mime_type']);

        $this->generateurSedaFillFiles->setNodeDoNotPutMineType("d539cccd-00c2-4a21-be54-e76aecfa2482", false);
        $this->assertEquals("", $this->getXMLFromGenerateur()->ArchiveUnit->File[0]['do_not_put_mime_type']);
    }

    public function testGetFiles()
    {
        $this->assertCount(1, $this->generateurSedaFillFiles->getFiles());
        $this->assertEquals(
            3,
            count($this->generateurSedaFillFiles->getFiles("b463da2d-08ca-4fb9-b787-2951ad5de015"))
        );
    }

    public function testGetArchiveUnit()
    {
        $this->assertCount(2, $this->generateurSedaFillFiles->getArchiveUnit());
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
        $xml =  $this->getXMLFromGenerateur();
        $this->generateurSedaFillFiles->upNode("bede30e3-3a93-4aa3-9f66-dbb516edc676");
        $this->assertEquals($xml->ArchiveUnit[1], $this->getXMLFromGenerateur()->ArchiveUnit[0]);
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
        $xml =  $this->getXMLFromGenerateur();
        $this->generateurSedaFillFiles->downNode("b463da2d-08ca-4fb9-b787-2951ad5de015");
        $this->assertEquals($xml->ArchiveUnit[1], $this->getXMLFromGenerateur()->ArchiveUnit[0]);
    }

    public function testUpNodeWhenNoFollowingSibling()
    {
        $this->generateurSedaFillFiles->downNode("7f0b47df-294b-4a1e-afed-1d31c96c234c");
        $this->assertXmlStringEqualsXmlFile(
            self::FIXTURE_FILEPATH,
            $this->getXMLFromGenerateur()->asXML()
        );
    }

    public function testSetArchiveUnitInfo()
    {
        $info = [
            'CustodialHistory' => 'historique de la conservation',
            'DescriptionLevel' => 'subgrp',
            'Language' => 'fra',
            'AccessRestrictionRule_AccessRule' => '',
            'AccessRestrictionRule_StartDate' => '',
            'ArchiveUnit_AppraisalRule_FinalAction' => '',
            'ArchiveUnit_AppraisalRule_Rule' => '',
            'ArchiveUnit_AppraisalRule_StartDate' => '',
            'Keywords' => '',
            'Description' => ''
        ];

        $this->generateurSedaFillFiles->setArchiveUnitInfo(
            "7f0b47df-294b-4a1e-afed-1d31c96c234c",
            $info
        );
        $this->assertEquals(
            $info,
            $this->generateurSedaFillFiles->getArchiveUnitSpecificInfo("7f0b47df-294b-4a1e-afed-1d31c96c234c")
        );
        //is idempotent
        $this->generateurSedaFillFiles->setArchiveUnitInfo(
            "7f0b47df-294b-4a1e-afed-1d31c96c234c",
            $info
        );
        $this->assertEquals(
            $info,
            $this->generateurSedaFillFiles->getArchiveUnitSpecificInfo("7f0b47df-294b-4a1e-afed-1d31c96c234c")
        );
    }
}
