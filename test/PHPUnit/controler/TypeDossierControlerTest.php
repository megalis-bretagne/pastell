<?php

class TypeDossierControlerTest extends ControlerTestCase {

	/**
	 * @throws Exception
	 */
	public function testExportAction(){
		/** @var TypeDossierControler $typeDossierControler */
		$typeDossierControler = $this->getControlerInstance(TypeDossierControler::class);

		$this->setGetInfo(['id_type_dossier'=>'phpunit-test-controller']);
		try {
			$typeDossierControler->doEditionAction();
			$this->assertFalse(true);
		} catch (Exception $e){
			$this->assertRegExp(
				"#Le type de dossier personnalisé <b>phpunit-test-controller</b> a été créé#",
				$e->getMessage()
			);
		}

		$typeDossierImportExport = $this->getObjectInstancier()->getInstance(TypeDossierImportExport::class);
		$typeDossierImportExport->setTimeFunction(function(){return "42";});


		$this->setGetInfo(['id_t'=>1]);
		$this->expectOutputString(
			file_get_contents(__DIR__."/fixtures/type-dossier-controler-export-expected.txt")
		);
		$typeDossierControler->exportAction();
	}

}