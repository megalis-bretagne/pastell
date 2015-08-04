<?php 

require_once __DIR__.'/../../init.php';
require_once( PASTELL_PATH.'/connecteur/mailsec/MailSec.class.php');


class MailSecTest extends PastellTestCase {
	
	public function reinitDatabaseOnSetup(){
		return true;
	}
	
	public function reinitFileSystemOnSetup(){
		return true;
	}
	
	/**
	 * @return DocumentEmail
	 */
	private function getDocumentEmail(){
		return $this->getObjectInstancier()->DocumentEmail;
	}
	
	/**
	 * @return ZenMail
	 */
	private function getZenMail(){
		$zenMail = new ZenMail(new FileContentType());
		$zenMail->disableMailSending();
		return $zenMail;
	}
	
	/**
	 * 
	 */
	public function getMailSec(ZenMail $zenMail){
		
		$mailsec = new MailSec($zenMail, $this->getDocumentEmail(), $this->getObjectInstancier()->Journal);
		
		$id_ce = $this->getObjectInstancier()->ConnecteurControler->nouveau(1,'mailsec',"Connecteur mailsec de test");
		
		$connecteurConfig = $this->getObjectInstancier()->ConnecteurFactory->getConnecteurConfig($id_ce);
		$mailsec->setConnecteurConfig($connecteurConfig);
		
		return $mailsec;
	}
	
	public function testSendAllMail(){
		$zenMail = $this->getZenMail();
		$email = "eric.pommateau@adullact-projet.com";
		$this->getDocumentEmail()->add(1, "eric.pommateau@adullact-projet.com", "to");
		
		$this->getMailSec($zenMail)->sendAllMail(1, 1);
		$all_info = $zenMail->getAllInfo();		
		$this->assertEquals(1, count($all_info));
		$this->assertEquals($email, $all_info[0][0]);
	}
	
	public function testSendOneMail(){
		$zenMail = $this->getZenMail();
		
		$email = "eric.pommateau@adullact-projet.com";
		$key = $this->getDocumentEmail()->add(1, "eric.pommateau@adullact-projet.com", "to");
		$document_email_info = $this->getDocumentEmail()->getInfoFromKey($key);
		
		$this->getMailSec($zenMail)->sendOneMail(1, 1, $document_email_info['id_de']);
		
		$all_info = $zenMail->getAllInfo();
		$this->assertEquals(1, count($all_info));
		$this->assertEquals($email, $all_info[0][0]);
		$info =$this->getDocumentEmail()->getInfoFromPK($document_email_info['id_de']);
		$this->assertEquals(1, $info['nb_renvoi']);
	}
	
}