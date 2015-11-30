<?php

class ZenMailTest extends PHPUnit_Framework_TestCase {

	public function testSetSujet(){
		$zenMail = new ZenMail(new FileContentType());
		$zenMail->setSujet("Sujet");
		$this->assertEquals($zenMail->getSujet(), "=?UTF-8?Q?Sujet?=");
	}
	
	public function testSetSujetAccent(){
		$zenMail = new ZenMail(new FileContentType());
		$zenMail->setSujet("Sujet à accent");
		$this->assertEquals($zenMail->getSujet(), "=?UTF-8?Q?Sujet=20=C3=A0=20accent?=");
	}
	
	public function testSetSujetLong(){
		$zenMail = new ZenMail(new FileContentType());
		$zenMail->setSujet("ceci est un très long sujet de mail envoyé par Pastell. De plus ce sujet contient aussi un accent");
		$this->assertEquals($zenMail->getSujet(), "=?UTF-8?Q?ceci=20est=20un=20tr=C3=A8s=20lo?==?UTF-8?Q?ng=20suj?=
 =?UTF-8?Q?et=20de=20mail=20envoy=C3=A9=20pa?==?UTF-8?Q?r=20Past?=
 =?UTF-8?Q?ell.=20De=20plus=20ce=20sujet?==?UTF-8?Q?=20contie?=
 =?UTF-8?Q?nt=20aussi=20un=20accent?=");
	}
	
}