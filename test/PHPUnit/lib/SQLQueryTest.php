<?php

class SQLQueryTest extends PastellTestCase {

    public function testWait(){
        $this->expectOutputString("MySQL est maintenant démarré");
        $this->getSQLQuery()->waitStarting(function($message){echo "$message";});
    }

    public function testWaitFailed(){
        $this->expectOutputRegex("#MySQL n'a pas démarré après 1 essai#");
        $sqlQuery = new SQLQuery("not existing","foo","bar");
        $sqlQuery->waitStarting(function($message){echo "$message";},0);
    }

    public function testGetClientEncoding(){
		$this->assertEquals('utf8',$this->getSQLQuery()->getClientEncoding());
	}


	public function testLogger(){
    	$sqlQuery  =$this->getObjectInstancier()->getInstance(SQLQuery::class);
    	$sqlQuery->setLogger($this->getLogger());
    	$nb_users = $sqlQuery->queryOne("SELECT count(*) FROM utilisateur");
		$this->assertEquals(2,$nb_users);
		/*$logs = $this->getLogRecords();
		$my_log = array_pop($logs);
		$this->assertEquals("SQL REQUEST : SELECT count(*) FROM utilisateur",$my_log['message']);*/
	}
}