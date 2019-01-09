<?php

class MailSecControlerTest extends PHPUnit\Framework\TestCase {


	protected function getMockObject($class_name){
		return $this->getMockBuilder($class_name)->disableOriginalConstructor()->getMock();
	}

	private function getMailSecControler(){

		$objectInstancier = new ObjectInstancier();

		$roleUtilisateur = $this->getMockObject("RoleUtilisateur");
		$roleUtilisateur->expects($this->any())->method("hasDroit")->willReturn(true);
		$objectInstancier->{'RoleUtilisateur'} = $roleUtilisateur;

		$entiteSQL = $this->getMockObject("EntiteSQL");
		$entiteSQL->expects($this->any())->method("getAncetre")->willReturn(array());
		$objectInstancier->{'EntiteSQL'} = $entiteSQL;

		$documentTypeFactory = $this->getMockObject("DocumentTypeFactory");
		$documentTypeFactory->expects($this->any())->method("getAllType")->willReturn(array());
		$objectInstancier->{'DocumentTypeFactory'} = $documentTypeFactory;

		$manifestReader = $this->getMockObject("ManifestReader");

		$manifestFactory = $this->getMockObject("ManifestFactory");
		$manifestFactory->expects($this->any())->method("getPastellManifest")->willReturn($manifestReader);
		$objectInstancier->{'ManifestFactory'} = $manifestFactory;


		$daemonManager = $this->getMockObject("DaemonManager");
		$objectInstancier->{'DaemonManager'} = $daemonManager;

		$annuaireSQL  = $this->getMockObject("AnnuaireSQL");
		$annuaireSQL->expects($this->any())->method("getUtilisateurList")->willReturn(array());
		$objectInstancier->{'AnnuaireSQL'} = $annuaireSQL;

		$sqlQuery = $this->getMockObject("SQLQuery");
		$objectInstancier->{'SQLQuery'} = $sqlQuery;

		$gabarit = $this->getMockObject("Gabarit");
		$objectInstancier->{'Gabarit'} = $gabarit;

		$formulaire = $this->getMockObject("Formulaire");

		$donneesFormulaire = $this->getMockObject("DonneesFormulaire");
		$donneesFormulaire->expects($this->any())->method("getFormulaire")->willReturn($formulaire);

		$donneesFormulaireFactory = $this->getMockObject("DonneesFormulaireFactory");
		$donneesFormulaireFactory->expects($this->any())->method("get")->willReturn($donneesFormulaire);

		$objectInstancier->{'DonneesFormulaireFactory'} = $donneesFormulaireFactory;

		$documentEmail = $this->getMockObject("DocumentEmail");
		$documentEmail->expects($this->any())->method("getInfoFromKey")->willReturn(array('id_d'=>42));
		$objectInstancier->{'DocumentEmail'} = $documentEmail;

		$journal = $this->getMockObject("Journal");
		$objectInstancier->setInstance(Journal::class,$journal);


		return new MailSecControler($objectInstancier);
	}

	public function testAnnuaire(){
		$mailseController = $this->getMailSecControler();
		$mailseController->annuaireAction();
		$view_parameter = $mailseController->getViewParameter();
		$this->assertEquals(0,$view_parameter['id_e']);
	}


	
}