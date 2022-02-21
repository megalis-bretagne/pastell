<?php

class NotificationTest extends PastellTestCase
{
    /**
     * @var Notification
     */
    private $notification;

    protected function setUp(): void
    {
        parent::setUp();
        $this->notification = new Notification($this->getSQLQuery());
    }

    public function testAdd()
    {
        $this->notification->add(1, 1, 'actes-generique', 'send-tdt', false);
        $info = $this->notification->getAll(1);
        $this->assertEquals('actes-generique', $info['1-actes-generique']['type']);
    }

    public function testAddTwoTimes()
    {
        $this->assertCount(0, $this->notification->getAll(1));
        $this->notification->add(1, 1, 'actes-generique', 'send-tdt', false);
        $this->assertCount(1, $this->notification->getAll(1));
        $this->notification->add(1, 1, 'actes-generique', 'send-tdt', false);
        $this->assertCount(1, $this->notification->getAll(1));
    }

    public function testHasDailyDigest()
    {
        $this->notification->add(1, 1, 'actes-generique', 'send-tdt', true);
        $this->assertEquals(1, $this->notification->hasDailyDigest(1, 1, 'actes-generique'));
    }

    public function testGetNotificationActionList()
    {
        $this->notification->add(1, 1, 'actes-generique', 'send-tdt', false);
        $info = $this->notification->getNotificationActionList(1, 1, 'actes-generique', array(array('id' => 'send-tdt')));
        $this->assertEquals(1, $info[0]['checked']);
    }

    public function testGetInfo()
    {
        $this->notification->add(1, 1, 'actes-generique', 'send-tdt', false);
        $all_info = $this->notification->getAllInfo(1, 'actes-generique', 'send-tdt');
        $id_n = $all_info[0]['id_n'];
        $info = $this->notification->getInfo($id_n);
        $this->assertEquals($all_info[0]['action'], $info['action']);
    }

    public function testRemove()
    {
        $this->notification->add(1, 1, 'actes-generique', 'send-tdt', false);
        $all_info = $this->notification->getAllInfo(1, 'actes-generique', 'send-tdt');
        $id_n = $all_info[0]['id_n'];
        $this->notification->remove($id_n);
        $all_info = $this->notification->getAllInfo(1, 'actes-generique', 'send-tdt');
        $this->assertEmpty($all_info);
    }

    public function testGetMail()
    {
        $this->notification->add(1, 1, 'actes-generique', 'send-tdt', false);
        $info = $this->notification->getMail(1, 'actes-generique', 'send-tdt');
        $this->assertEquals(array("eric@sigmalis.com"), $info);
    }

    public function testRemoveAll()
    {
        $this->notification->add(1, 1, 'actes-generique', 'send-tdt', false);
        $this->notification->removeAll(1, 1, 'actes-generique');
        $all_info = $this->notification->getAllInfo(1, 'actes-generique', 'send-tdt');
        $this->assertEmpty($all_info);
    }

    public function testToogleDailyDigest()
    {
        $this->notification->add(1, 1, 'actes-generique', 'send-tdt', false);
        $this->notification->toogleDailyDigest(1, 1, 'actes-generique');
        $all_info = $this->notification->getAllInfo(1, 'actes-generique', 'send-tdt');
        $this->assertEquals(1, $all_info[0]['daily_digest']);
    }
}
