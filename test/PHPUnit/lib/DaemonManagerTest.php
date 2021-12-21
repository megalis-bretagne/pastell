<?php

class DaemonManagerTest extends PHPUnit\Framework\TestCase
{
    /** @var  DaemonManager */
    private $daemonManager;

    protected function setUp()
    {
        $tmp_dir = sys_get_temp_dir();

        $pid_file = $tmp_dir . "/" . uniqid("pastell_daemon_manager_test_pid_file_");
        $log_file = $tmp_dir . "/" . uniqid("pastell_daemon_manager_test_log_file_");
        $user = get_current_user();

        $fake_daemon_command = PHP_PATH . " " . __DIR__ . "/fixtures/fake_daemon.php";

        $this->daemonManager = new DaemonManager($fake_daemon_command, $pid_file, $log_file, $user);
    }

    public function testStatus()
    {
        $this->assertEquals(DaemonManager::IS_STOPPED, $this->daemonManager->status());
    }

    /*public function testStart(){
        $this->daemonManager->start();
        $this->assertEquals(DaemonManager::IS_RUNNING,$this->daemonManager->status());
    }

    public function testStartTwice(){
        $this->daemonManager->start();
        $pid = $this->daemonManager->getDaemonPID();
        $this->daemonManager->start();
        $this->assertEquals($pid,$this->daemonManager->getDaemonPID());
    }

    public function testStartAsAnotherUser(){
        //Heuh ben ca marche pas s'il y a qu'un user...
        $uid = getmyuid();
        $this->daemonManager->setUser($uid);
        $this->expectOutputString("Starting daemon as $uid\n");
        $this->daemonManager->start();
        $this->assertEquals(DaemonManager::IS_STOPPED,$this->daemonManager->status());

    }

    public function testUnableToWritePIDFile(){
        $this->daemonManager->setPidFile("test://file_not_existing");
        $this->setExpectedException("Exception","Impossible d'écrire le fichier test://file_not_existing");
        $this->daemonManager->start();
    }

    public function testRestart(){
        $this->daemonManager->restart();
        $this->assertEquals(DaemonManager::IS_RUNNING,$this->daemonManager->status());
    }

    public function testStop(){
        $this->daemonManager->start();
        $this->daemonManager->stop(); //Attention sleep(1) !
        $this->assertEquals(DaemonManager::IS_STOPPED,$this->daemonManager->status());
    }*/
}
