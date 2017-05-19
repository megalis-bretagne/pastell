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

}