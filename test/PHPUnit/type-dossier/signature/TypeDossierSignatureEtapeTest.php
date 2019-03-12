<?php

class TypeDossierSignatureEtapeTest extends PastellTestCase {

	public function testHasDateLimite(){
		$typeDossierTranslator = $this->getObjectInstancier()->getInstance(TypeDossierTranslator::class);
		$typeDossierData = new TypeDossierData();
		$typeDossierData->etape[] = new TypeDossierEtape();
		$typeDossierData->etape[0]->type = 'signature';
		$typeDossierData->etape[0]->specific_type_info['has-date-limite'] = 'on';
		$typeDossierData->etape[0]->specific_type_info['libelle_parapheur'] = 'objet';
		$typeDossierData->etape[0]->specific_type_info['document_a_signer'] = 'document';
		$typeDossierData->etape[0]->specific_type_info['annexe'] = 'annexe';

		$result = $typeDossierTranslator->getDefinition($typeDossierData);
		$this->assertEquals('Utiliser une date limite',$result['formulaire']['i-Parapheur']['has_date_limite']['name']);
	}

	public function testHasNoDateLimite(){
		$typeDossierTranslator = $this->getObjectInstancier()->getInstance(TypeDossierTranslator::class);
		$typeDossierData = new TypeDossierData();
		$typeDossierData->etape[] = new TypeDossierEtape();
		$typeDossierData->etape[0]->type = 'signature';
		$typeDossierData->etape[0]->specific_type_info['has-date-limite'] = '';
		$typeDossierData->etape[0]->specific_type_info['libelle_parapheur'] = 'objet';
		$typeDossierData->etape[0]->specific_type_info['document_a_signer'] = 'document';
		$typeDossierData->etape[0]->specific_type_info['annexe'] = 'annexe';


		$result = $typeDossierTranslator->getDefinition($typeDossierData);
		$this->assertFalse(isset($result['formulaire']['i-Parapheur']['has_date_limite']));
	}

}