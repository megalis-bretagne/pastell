<?php

require_once __DIR__."/../../../../../module/actes-generique/lib/ActesTypePJData.class.php";
require_once __DIR__."/../../../../../module/actes-generique/lib/ActesTypePJ.class.php";

class ActesTypePJTest extends PHPUnit\Framework\TestCase {

	/**
	 * @throws Exception
	 */
	public function testGetTypePJListe(){

		$actesTypePJData = new ActesTypePJData();

		$actesTypePJData->classification_file_path = __DIR__."/../fixtures/classification.xml";
		$actesTypePJData->acte_nature = 4;
		$actesTypePJData->actes_matiere1 = 1;
		$actesTypePJData->actes_matiere2 = 1;

		$actesTypePJ = new ActesTypePJ();
		$result = $actesTypePJ->getTypePJListe($actesTypePJData);
		$expected_value = array (
			'10_DE' => 'Délibération autorisant à passer le contrat',
			'11_AP' => 'Cahier des clauses administratives particulières',
			'11_AV' => 'Avis du jury de concours',
			'11_IN' => 'Invitation des candidats à soumissionner',
			'11_JU' => 'Rapport justifiant le choix du marché, les modalités et la procédure de passation',
			'11_RC' => 'Règlement de consultation',
			'11_TP' => 'Cahier des clauses techniques particulières',
			'99_CO' => 'Contrat',
			'99_AU' => 'Autre Document',
		);
		$this->assertEquals($expected_value,$result);
	}
}