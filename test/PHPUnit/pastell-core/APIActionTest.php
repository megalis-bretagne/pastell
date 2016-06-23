<?php

class APIActionTest extends PastellTestCase {

	/**
	 * @var APIAction
	 */
	private $apiAction;

	protected function setUp(){
		parent::setUp();
		$this->apiAction = new APIAction($this->getObjectInstancier(), 1);
	}

	public function testDetailDocument(){
		$info = $this->apiAction->createDocument(1,'mailsec');
		$result = $this->apiAction->detailDocument(1,$info['id_d']);
		$this->assertEquals('mailsec',$result['info']['type']);
	}

	public function testDetailConnecteurEntite(){
		$info = $this->apiAction->detailConnecteurEntite(1,11);
		$this->assertEquals("mailsec",$info['id_connecteur']);
	}

	public function  testDetailSeveralDocument(){
		$info = $this->apiAction->createDocument(1,'mailsec');
		$id_d_list[] = $info['id_d'];
		$info = $this->apiAction->createDocument(1,'mailsec');
		$id_d_list[] = $info['id_d'];
		$info = $this->apiAction->detailSeveralDocument(1,$id_d_list);
		$this->assertEquals('mailsec',$info[$id_d_list[1]]['info']['type']);
	}


	private function createDocumentModificationNoChangeEtat($etat_modif){
		$ymlLoader = new YMLLoader(new MemoryCacheNone());
		$flux = $ymlLoader->getArray(__DIR__."/fixtures/definition-with-modification-no-change-etat.yml");

		$documentTypeFactory = $this->getMockBuilder("DocumentTypeFactory")->disableOriginalConstructor()->getMock();
		$documentTypeFactory
			->expects($this->any())
			->method("getFluxDocumentType")
			->willReturn(new DocumentType('mailsec',$flux));

		$this->getObjectInstancier()->DocumentTypeFactory = $documentTypeFactory;

		$info = $this->apiAction->createDocument(self::ID_E_COL,'mailsec');
		$info['id_e'] = self::ID_E_COL;
		$info['test'] = 'chaîne de test';

		$this->apiAction->action(self::ID_E_COL,$info['id_d'],$etat_modif);
		$this->apiAction->modifDocument($info);
		return $this->apiAction->detailDocument(1,$info['id_d']);
	}

	public function testModifDocumentChangeEtat(){
		$detail_info = $this->createDocumentModificationNoChangeEtat('modification-changement');
		$this->assertEquals('modification',$detail_info['last_action']['action']);
	}

	public function testModifDocumentNoChangeEtat(){
		$detail_info = $this->createDocumentModificationNoChangeEtat('modification-pas-de-changement');
		$this->assertEquals('modification-pas-de-changement',$detail_info['last_action']['action']);
	}

}