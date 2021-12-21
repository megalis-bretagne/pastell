<?php

class JobTest extends PastellTestCase
{
    /** @var  Job */
    private $job;

    protected function setUp()
    {
        parent::setUp();
        $this->job = new Job();
    }

    public function testGetLastMessage()
    {
        $this->job->last_message = "toto";
        $this->assertEquals("toto", $this->job->getLastMessage());
    }

    public function testTooLarge()
    {
        $this->job->last_message = str_repeat("a", Job::MAX_LAST_MESSAGE_LENGTH + 10);
        $this->assertEquals(Job::MAX_LAST_MESSAGE_LENGTH, strlen($this->job->getLastMessage()));
    }
}
