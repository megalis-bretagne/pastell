<?php

class NotificationDigestSQLTest extends PastellTestCase
{
    /** @var  NotificationDigestSQL */
    private $notificationDigestSQL;

    protected function setUp(): void
    {
        parent::setUp();
        $this->notificationDigestSQL = new NotificationDigestSQL($this->getSQLQuery());
        $this->notificationDigestSQL->add("foo@bar.baz", 1, "xyzt", "test", "type", "ceci est un message");
    }

    public function testGetAll()
    {
        $this->assertEquals("ceci est un message", $this->notificationDigestSQL->getAll()['foo@bar.baz'][0]['message']);
    }

    public function testDelete()
    {
        $id_nd = $this->notificationDigestSQL->getAll()['foo@bar.baz'][0]['id_nd'];
        $this->notificationDigestSQL->delete($id_nd);
        $this->assertEmpty($this->notificationDigestSQL->getAll());
    }
}
