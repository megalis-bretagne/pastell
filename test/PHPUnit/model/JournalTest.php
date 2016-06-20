<?php


class JournalTest extends PastellTestCase {

	/**
	 * @var Journal
	 */
	private $journal;

	protected function setUp(){
		parent::setUp();

		$this->journal = $this->getJournal();
		$horodateur = new MockHorodateur($this->getObjectInstancier()->getInstance('OpensslTSWrapper'));
		$this->journal->setHorodateur($horodateur);
	}

	public function getJournal(){
		$journal = new Journal(
			$this->getSQLQuery(),
			$this->getObjectInstancier()->getInstance("Utilisateur"),
			$this->getObjectInstancier()->getInstance("Document"),
			$this->getObjectInstancier()->getInstance('DocumentTypeFactory')
		);
		$journal->setId(1);
		return $journal;
	}


	public function testAddConsulter(){
		$id_j = $this->journal->addConsultation(1,'XYZT',1);
		$info = $this->journal->getInfo($id_j);
		$this->assertEquals("Eric Pommateau a consulté le document",$info['message']);
	}

	public function testAddSameConsulter(){
		$this->journal->addConsultation(1,'XYZT',1);
		$this->assertFalse($this->journal->addConsultation(1,'XYZT',1));
	}

	public function testAddActionAuto(){
		$id_j = $this->journal->addActionAutomatique(Journal::DOCUMENT_ACTION,1,"XYZT","test","message de test");
		$info = $this->journal->getInfo($id_j);
		$this->assertEquals(0,$info['id_u']);
	}

	public function testAddSQL(){
		$id_j = $this->journal->addSQL(false,false,false,false,false,false);
		$info = $this->journal->getInfo($id_j);
		$this->assertEquals("MOCK TIMESTAMP",$info['preuve']);
	}

	public function testAddSQLWihtoutHorodateur(){
		$journal = $this->getJournal();
		$id_j = $journal->addSQL(false,false,false,false,false,false);
		$info = $journal->getInfo($id_j);
		$this->assertEquals("",$info['preuve']);
	}

	public function testGetAll(){
		$this->journal->addConsultation(1,"XYZ",1);
		$info = $this->journal->getAll(1,false,false,1,0,10,"consulté");
		$this->assertEquals("Eric Pommateau a consulté le document",$info[0]['message']);
	}

	public function testGetAllAllInfo(){
		$this->journal->addConsultation(1,"XYZ",1);
		$info = $this->journal->getAll(1,"test",1,1,0,10,"consulté","2015-01-01","2015-01-02",true);
		$this->assertEmpty($info);
	}

	public function testCountAll(){
		$this->journal->addConsultation(1,"XYZ",1);
		$this->assertEquals(1,$this->journal->countAll(1,false,false,1,"consulté",false,false));
	}
	public function testCountAllEmpty(){
		$this->journal->addConsultation(1,"XYZ",1);
		$this->assertEquals(0,$this->journal->countAll(1,"test",1,1,"consulté","2015-01-01","2015-01-02"));
	}

	public function testGetTypeAsString(){
		$this->assertEquals("Connexion",$this->journal->getTypeAsString(Journal::CONNEXION));
	}

	public function testGetAllInfo(){
		$id_j = $this->journal->addConsultation(1,"XYZ",1);
		$info = $this->journal->getAllInfo($id_j);
		$this->assertEquals("Eric Pommateau a consulté le document",$info['message']);
	}

	public function testGetAllInfoNotExisting(){
		$this->assertFalse($this->journal->getAllInfo(42));
	}

	public function testHorodateAllNoHorodateur(){
		$journal = $this->getJournal();
		$this->setExpectedException("Exception","Aucun horodateur configuré");
		$journal->horodateAll();
	}

	public function testHorodateAll(){
		$journal = $this->getJournal();
		$id_j = $journal->addConsultation(1,"XYZ",1);

		$info = $journal->getInfo($id_j);
		$this->assertEquals("",$info['preuve']);

		$this->expectOutputString("1 horodaté : 1977-02-18 08:40:00\n");
		$this->journal->horodateAll();

		$info = $journal->getInfo($id_j);
		$this->assertEquals("MOCK TIMESTAMP",$info['preuve']);
	}

}

class MockHorodateur extends Horodateur {

	public function setConnecteurConfig(DonneesFormulaire $donneesFormulaire){}

	public function getTimestampReply($timestamp_reply){
		return "MOCK TIMESTAMP";
	}

	public function getTimeStamp($timestamp){
		return "1977-02-18 08:40:00";
	}

	public function verify($data,$token){
		return true;
	}

}