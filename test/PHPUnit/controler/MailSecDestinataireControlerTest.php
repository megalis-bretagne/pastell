<?php

class MailSecDestinataireControlerTest extends ControlerTestCase {

	private function createMailSec(){
		$this->loadExtension([__DIR__."/../fixtures/mailsec-bidir-test"]);
		// Par défaut, on a un cache statique pour le chargement du YAML...
		$this->getObjectInstancier()->getInstance(MemoryCache::class)->flushAll();

		$roleSQL = $this->getObjectInstancier()->getInstance(RoleSQL::class);

		$roleSQL->addDroit('admin',"mailsec-bidir-test:lecture");
		$roleSQL->addDroit('admin',"mailsec-bidir-test:edition");


		$id_ce = 11;
		$this->getInternalAPI()->post(
			"/entite/" . self::ID_E_COL . "/flux/mailsec-bidir-test/connecteur/$id_ce",
			[
				'type' => 'mailsec'
			]
		);

		$info = $this->getInternalAPI()->post("entite/1/document",array('type'=>'mailsec-bidir-test'));
		$id_d = $info['id_d'];
		$this->getInternalAPI()->patch("entite/1/document/$id_d",[
			'objet'=>'test de mail',
			'to'=>"test@libriciel.fr",
			'message'=>'message de test'
		]);

		$this->getInternalAPI()->post("/entite/1/document/$id_d/action/envoi-mail");

		$info = $this->getObjectInstancier()->getInstance(DocumentEmail::class)->getInfo($id_d);

		$key = $info[0]['key'];

		return ['id_d'=> $id_d,'key'=>$key];

	}

	/**
	 * @throws Exception
	 */
	public function testIndexAction(){

		$info = $this->getInternalAPI()->post("entite/1/document",array('type'=>'mailsec'));

		$id_d = $info['id_d'];

		$this->getInternalAPI()->patch("entite/1/document/$id_d",[
			'objet'=>'test de mail',
			'to'=>"test@libriciel.fr",
			'message'=>'message de test'
		]);

		$this->getInternalAPI()->post("/entite/1/document/$id_d/action/envoi");

		$info = $this->getObjectInstancier()->getInstance(DocumentEmail::class)->getInfo($id_d);

		$key = $info[0]['key'];


		$mailseController = $this->getControlerInstance(MailSecDestinataireControler::class);

		$this->setGetInfo(['key'=>$key]);
		$mailseController->setServerInfo(['REMOTE_ADDR'=>'127.0.0.1']);

		ob_start();
		$mailseController->indexAction();
		ob_end_clean();
		$view_parameter = $mailseController->getViewParameter();
		$this->assertEquals($key,$view_parameter['mailSecInfo']->key);
		$this->assertEquals($id_d,$view_parameter['mailSecInfo']->id_d);
	}

	/**
	 * @throws Exception
	 */
	public function testRepondreAction(){

		$mail_sec_info  = $this->createMailSec();
		$key = $mail_sec_info['key'];
		$id_d = $mail_sec_info['id_d'];


		/** @var MailSecDestinataireControler $mailseController */
		$mailseController = $this->getControlerInstance(MailSecDestinataireControler::class);

		$this->setGetInfo(['key'=>$key]);
		$mailseController->setServerInfo(['REMOTE_ADDR'=>'127.0.0.1']);


		ob_start();
		$mailseController->repondreAction();
		ob_end_clean();
		$view_parameter = $mailseController->getViewParameter();
		/** @var MailSecInfo $mailSecInfo */
		$mailSecInfo = $view_parameter['mailSecInfo'];
		$this->assertEquals($key,$view_parameter['mailSecInfo']->key);
		$this->assertEquals($id_d,$view_parameter['mailSecInfo']->id_d);
		$this->assertEquals('mailsec-bidir-test-reponse',$mailSecInfo->flux_reponse);
	}


	/**
	 * @throws Exception
	 */
	public function testreponseEditionAction(){
		$mail_sec_info  = $this->createMailSec();
		$key = $mail_sec_info['key'];
		$id_d = $mail_sec_info['id_d'];


		/** @var MailSecDestinataireControler $mailseController */
		$mailseController = $this->getControlerInstance(MailSecDestinataireControler::class);
		$this->setPostInfo(['reponse'=>'ceci est ma réponse','key'=>$key]);
		$mailseController->setServerInfo(['REMOTE_ADDR'=>'127.0.0.1','REQUEST_METHOD'=>'POST']);

		ob_start();
		$mailseController->repondreAction();
		try {
			$mailseController->reponseEditionAction();
		} catch (Exception $e){}
		$mailseController->validationAction();
		try {
			$this->setPostInfo(['key' => $key]);
			$mailseController->doValidationAction();
		} catch (Exception $e){}
		ob_end_clean();

		$documentEmail = $this->getObjectInstancier()->getInstance(DocumentEmail::class);
		$info = $documentEmail->getInfoFromKey($key);
		$id_de = $info['id_de'];

		$documentEmailReponseSQL = $this->getObjectInstancier()->getInstance(DocumentEmailReponseSQL::class);
		$this->assertEquals('ceci est ma réponse',$documentEmailReponseSQL->getAllReponse($id_d)[$id_de]['titre']);
	}

	/**
	 * @throws Exception
	 */
	public function testRecuperationFichierAction(){
		$mail_sec_info  = $this->createMailSec();
		$key = $mail_sec_info['key'];
		$id_d = $mail_sec_info['id_d'];

		$donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);
		$donneesFormulaire->addFileFromData('document_attache','foo.txt','bar');

		/** @var MailSecDestinataireControler $mailseController */
		$mailseController = $this->getControlerInstance(MailSecDestinataireControler::class);

		$this->setGetInfo(['key'=>$key,'field'=>'document_attache']);
		$mailseController->setServerInfo(['REMOTE_ADDR'=>'127.0.0.1']);

		ob_start();
		$mailseController->recuperationFichierAction();
		$output = ob_get_clean();
		$this->assertEquals('Content-type: text/plain
Content-disposition: attachment; filename="foo.txt"
Expires: 0
Cache-Control: must-revalidate, post-check=0,pre-check=0
Pragma: public
bar',$output);
	}

	/**
	 * @throws Exception
	 */
	public function testsuppressionFichierAction(){
		$mail_sec_info  = $this->createMailSec();
		$key = $mail_sec_info['key'];

		/** @var MailSecDestinataireControler $mailseController */
		$mailseController = $this->getControlerInstance(MailSecDestinataireControler::class);
		$this->setPostInfo(['reponse'=>'ceci est ma réponse','key'=>$key]);
		$mailseController->setServerInfo(['REMOTE_ADDR'=>'127.0.0.1','REQUEST_METHOD'=>'POST']);

		ob_start();
		$mailseController->repondreAction();
		try {
			$mailseController->reponseEditionAction();
		} catch (Exception $e){}
		ob_end_clean();

		$documentEmail = $this->getObjectInstancier()->getInstance(DocumentEmail::class);
		$info = $documentEmail->getInfoFromKey($key);
		$id_de = $info['id_de'];

		$documentEmailReponseSQL = $this->getObjectInstancier()->getInstance(DocumentEmailReponseSQL::class);
		$id_d_reponse = $documentEmailReponseSQL->getInfo($id_de)['id_d_reponse'];

		$donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d_reponse);
		$donneesFormulaire->addFileFromData('document_attache','foo.txt','bar');


		$this->setPostInfo(['key'=>$key,'field'=>'document_attache','fichier_reponse'=>1]);
		ob_start();
		$mailseController->recuperationFichierAction();

		$output = ob_get_clean();
		$this->assertEquals('Content-type: text/plain
Content-disposition: attachment; filename="foo.txt"
Expires: 0
Cache-Control: must-revalidate, post-check=0,pre-check=0
Pragma: public
bar',$output);

		try {
			$mailseController->suppressionFichierAction();
		} catch (Exception $e){}

		$this->expectExceptionMessage("Ce fichier n'existe pas");
		$mailseController->recuperationFichierAction();
	}

	public function testPasswordAction(){
		$mail_sec_info  = $this->createMailSec();
		$key = $mail_sec_info['key'];

		/** @var MailSecDestinataireControler $mailsecController */
		$mailsecController = $this->getControlerInstance(MailSecDestinataireControler::class);
		$this->setGetInfo(['key'=>$key]);

		$this->expectOutputRegex("#<input type='password' name='password' />#");
		$mailsecController->passwordAction();
	}

	public function testInvalidAction(){
		$mail_sec_info  = $this->createMailSec();
		$key = $mail_sec_info['key'];

		/** @var MailSecDestinataireControler $mailsecController */
		$mailsecController = $this->getControlerInstance(MailSecDestinataireControler::class);
		$this->setGetInfo(['key'=>$key]);

		$this->expectOutputRegex("#La clé du message ne correspond à aucun mail sécurisé#");
		$mailsecController->invalidAction();
	}

}