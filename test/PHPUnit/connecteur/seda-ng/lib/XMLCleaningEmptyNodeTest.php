<?php

class XMLCleaningEmptyNodeTest extends PHPUnit\Framework\TestCase
{
    private function cleanTesting($input, $expected_output)
    {
        $domDocument = new DOMDocument();
        $domDocument->loadXML($input);

        $xmlCleanEmptyNode = new XMLCleaningEmptyNode();
        $xmlCleanEmptyNode->clean($domDocument);

        $output = $domDocument->saveXML($domDocument->documentElement);
        $this->assertEquals($expected_output, $output);
    }

    public function testNotModify()
    {
        $input = "<toto><titi>test</titi></toto>";
        $this->cleanTesting($input, $input);
    }

    public function testEmtpyOneNode()
    {
        $input = "<pim><pam>test</pam><poum></poum></pim>";
        $output = "<pim><pam>test</pam></pim>";
        $this->cleanTesting($input, $output);
    }

    public function testEmptyNodeWithNamespace()
    {
        $input = "<pim xmlns='https://toto'><pam>test</pam><poum></poum></pim>";
        $output = "<pim xmlns=\"https://toto\"><pam>test</pam></pim>";
        $this->cleanTesting($input, $output);
    }

    public function testEmptyNodeWithNamedNamespace()
    {
        $input = "<pim xmlns:toto='https://toto'><toto:pam>test</toto:pam><toto:poum/></pim>";
        $output = "<pim xmlns:toto=\"https://toto\"><toto:pam>test</toto:pam></pim>";
        $this->cleanTesting($input, $output);
    }

    public function testNodeWithNode()
    {
        $input = '<pim><pam>test</pam><poum><toto attr="value"/></poum></pim>';
        $this->cleanTesting($input, $input);
    }

    public function testEmptyNodeCascade()
    {
        $input = "<pim><pam>test</pam><poum><toto></toto></poum></pim>";
        $output = "<pim><pam>test</pam></pim>";
        $this->cleanTesting($input, $output);
    }

    public function testEmptyAttr()
    {
        $input = '<pim><pam>test</pam><poum><toto attr=""/></poum></pim>';
        $output = "<pim><pam>test</pam></pim>";
        $this->cleanTesting($input, $output);
    }
}
