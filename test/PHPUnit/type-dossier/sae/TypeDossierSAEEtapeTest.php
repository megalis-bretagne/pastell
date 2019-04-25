<?php

class TypeDossierSAEEtapeTest extends PastellTestCase {

    public function testSetSpecificInfo(){
        $typeDossierTranslator = $this->getObjectInstancier()->getInstance(TypeDossierTranslator::class);
        $typeDossierData = new TypeDossierProperties();
        $typeDossierData->etape[] = new TypeDossierEtapeProperties();
        $typeDossierData->etape[0]->type = 'sae';
        $result = $typeDossierTranslator->getDefinition($typeDossierData);
        $this->assertFalse(isset($result['page-condition']['Configuration SAE']));
    }

    public function testHasConfigurationSAE(){
        $typeDossierTranslator = $this->getObjectInstancier()->getInstance(TypeDossierTranslator::class);
        $typeDossierData = new TypeDossierProperties();
        $typeDossierData->etape[] = new TypeDossierEtapeProperties();
        $typeDossierData->etape[0]->type = 'sae';
        $typeDossierData->etape[0]->specific_type_info['sae-has-metadata-in-json'] = 'On';
        $result = $typeDossierTranslator->getDefinition($typeDossierData);
        $this->assertTrue(isset($result['page-condition']['Configuration SAE']));
    }

}