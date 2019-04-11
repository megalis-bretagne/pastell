<?php

class TypeDossierImportExportTest extends PastellTestCase {

	const FIXTURE_FILE = __DIR__."/fixtures/arrete-rh.json";
	const ID_TYPE_DOSSIER = 'arrete-rh';

	/**
	 * @throws UnrecoverableException
	 */
	public function testImport(){
		$typeDossierImportExport = $this->getObjectInstancier()->getInstance(TypeDossierImportExport::class);
		$result = $typeDossierImportExport->import(file_get_contents(self::FIXTURE_FILE));
		unset($result[TypeDossierImportExport::TIMESTAMP]);
		$this->assertEquals(
		array (
			'id_t' => '1',
			'id_type_dossier' =>  self::ID_TYPE_DOSSIER,
			'orig_id_type_dossier' => self::ID_TYPE_DOSSIER,
		),$result);
	}

	/**
	 * @throws UnrecoverableException
	 */
	public function testImportExport(){
		$typeDossierImportExport = $this->getObjectInstancier()->getInstance(TypeDossierImportExport::class);
		$result = $typeDossierImportExport->import(file_get_contents(self::FIXTURE_FILE));

		$typeDossierImportExport = $this->getObjectInstancier()->getInstance(TypeDossierImportExport::class);
		$typeDossierImportExport->setTimeFunction(function() use($result) {return $result[TypeDossierImportExport::TIMESTAMP];});

		$result2 = $typeDossierImportExport->export($result['id_t']);
		$this->assertEquals(json_decode(file_get_contents(self::FIXTURE_FILE),true),json_decode($result2,true));
	}

	/**
	 * @throws UnrecoverableException
	 */
	public function testImportWhenNoContent(){
		$typeDossierImportExport = $this->getObjectInstancier()->getInstance(TypeDossierImportExport::class);
		$this->expectException(UnrecoverableException::class);
		$this->expectExceptionMessage("Aucun fichier n'a été présenté ou le fichier est vide");
		$typeDossierImportExport->import("");
	}

	/**
	 * @throws UnrecoverableException
	 */
	public function testImportWhenNoJson(){
		$typeDossierImportExport = $this->getObjectInstancier()->getInstance(TypeDossierImportExport::class);
		$this->expectException(UnrecoverableException::class);
		$this->expectExceptionMessage("Le fichier présenté ne contient pas de json");
		$typeDossierImportExport->import("foo");
	}

	/**
	 * @throws UnrecoverableException
	 */
	public function testImportWhenJsonIsNotExploitable(){
		$typeDossierImportExport = $this->getObjectInstancier()->getInstance(TypeDossierImportExport::class);
		$this->expectException(UnrecoverableException::class);
		$this->expectExceptionMessage("Le fichier présenté ne semble pas contenir de données utilisatbles");
		$typeDossierImportExport->import('{"toto":"toto"}');
	}

	/**
	 * @throws UnrecoverableException
	 */
	public function testDoubleImport(){
		$typeDossierImportExport = $this->getObjectInstancier()->getInstance(TypeDossierImportExport::class);

		$typeDossierImportExport->import(file_get_contents(self::FIXTURE_FILE));
		$result = $typeDossierImportExport->import(file_get_contents(self::FIXTURE_FILE));
		unset($result[TypeDossierImportExport::TIMESTAMP]);
		$this->assertEquals(
			array (
				'id_t' => '2',
				'id_type_dossier' => 'arrete-rh_1',
				'orig_id_type_dossier' => self::ID_TYPE_DOSSIER,
			),$result);
	}



}