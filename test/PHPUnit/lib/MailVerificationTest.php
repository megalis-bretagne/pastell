<?php

class MailVerificationTest extends PHPUnit_Framework_TestCase {

	public function testSend(){
		$zenMail = $this->getMockBuilder('ZenMail')->disableOriginalConstructor()->getMock();
		$mailVerification = new MailVerification($zenMail);
		$mailVerification->send(array("email"=>"test"));
	}

}