<?php 

require_once( PASTELL_PATH.'/connecteur/mailsec/MailSec.class.php');


class MailSecTest extends PastellTestCase {

	const FLUX_ID =  'mailsec';

	/**
	 * @return DocumentEmail
	 */
	private function getDocumentEmail(){
		return $this->getObjectInstancier()->{'DocumentEmail'};
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
	 * @param ZenMail $zenMail
	 * @return MailSec
	 */
	public function getMailSec(ZenMail $zenMail){
		$mailsec = new MailSec(
			$zenMail,
			$this->getDocumentEmail(),
			$this->getJournal(),
			$this->getObjectInstancier()->{'EntiteSQL'}
		);

		/** @var ConnecteurControler $connecteurControler */
		$connecteurControler = $this->getObjectInstancier()->{'ConnecteurControler'};
		$id_ce = $connecteurControler->nouveau(1,'mailsec',"Connecteur mailsec de test");

		$connecteurConfig = $this->getConnecteurFactory()->getConnecteurConfig($id_ce);
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
		$info = $this->getDocumentEmail()->getInfoFromPK($document_email_info['id_de']);
		$this->assertEquals(1, $info['nb_renvoi']);
	}

	public function testReplaceTitle(){
		$info = $this->getAPIAction()->createDocument(PastellTestCase::ID_E_COL, self::FLUX_ID);
		$info['id_e'] = PastellTestCase::ID_E_COL;
		$info['to'] = "eric.pommateau@adullact-projet.coop";
		$info['objet'] = "titré du messàge";
		$this->getAPIAction()->modifDocument($info);

		$connecteurConfig = $this->getConnecteurFactory()->getConnecteurConfigByType(PastellTestCase::ID_E_COL,'mailsec','mailsec');
		$connecteurConfig->setData('mailsec_subject','Titré: %TITRE% %ENTITE%');
		$connecteurConfig->setData('mailsec_content','Content: %TITRE% the content %ENTITE%');

		/** @var ZenMail $zenMail */
		$zenMail = $this->getObjectInstancier()->getInstance('ZenMail');
		$zenMail->disableMailSending();
		$this->getAPIAction()->action(PastellTestCase::ID_E_COL,$info['id_d'],'envoi');

		$sujet = $zenMail->getSujet();
		$sujet = utf8_encode(iconv_mime_decode($sujet));
		$this->assertEquals("Titré: ".$info['objet']." Bourg-en-Bresse",$sujet);

		$contenu = $zenMail->getContenu();
		$this->assertRegExp("#{$info['objet']}#",$contenu);
		$this->assertRegExp("#Bourg-en-Bresse#",$contenu);
	}


}