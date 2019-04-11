<?php

class TypeDossierSignatureEtapeTest extends PastellTestCase {

	public function testHasDateLimite(){
		$typeDossierTranslator = $this->getObjectInstancier()->getInstance(TypeDossierTranslator::class);
		$typeDossierData = new TypeDossierProperties();
		$typeDossierData->etape[] = new TypeDossierEtapeProperties();
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
		$typeDossierData = new TypeDossierProperties();
		$typeDossierData->etape[] = new TypeDossierEtapeProperties();
		$typeDossierData->etape[0]->type = 'signature';
		$typeDossierData->etape[0]->specific_type_info['has-date-limite'] = '';
		$typeDossierData->etape[0]->specific_type_info['libelle_parapheur'] = 'objet';
		$typeDossierData->etape[0]->specific_type_info['document_a_signer'] = 'document';
		$typeDossierData->etape[0]->specific_type_info['annexe'] = 'annexe';


		$result = $typeDossierTranslator->getDefinition($typeDossierData);
		$this->assertFalse(isset($result['formulaire']['i-Parapheur']['has_date_limite']));
	}

	public function testGetSpecific(){
		$typeDossierTranslator = $this->getObjectInstancier()->getInstance(TypeDossierTranslator::class);
		$typeDossierData = new TypeDossierProperties();
		$typeDossierData->etape[] = new TypeDossierEtapeProperties();
		$typeDossierData->etape[0]->type = 'signature';
		$typeDossierData->etape[0]->specific_type_info['has-date-limite'] = '';
		$typeDossierData->etape[0]->specific_type_info['libelle_parapheur'] = 'objet';
		$typeDossierData->etape[0]->specific_type_info['document_a_signer'] = 'document';
		$typeDossierData->etape[0]->specific_type_info['annexe'] = 'annexe';
		$typeDossierData->etape[0]->etape_with_same_type_exists = true;

		$result = $typeDossierTranslator->getDefinition($typeDossierData);
		$this->assertEquals('objet',$result['action']['send-iparapheur_1']['connecteur-type-mapping']['objet']);
		$this->assertEquals(1,$result['page-condition']['Signature #1']['has_signature_1']);

	}

}