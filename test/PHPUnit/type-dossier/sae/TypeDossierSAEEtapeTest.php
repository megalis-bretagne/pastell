<?php

class TypeDossierSAEEtapeTest extends PastellTestCase
{
    public function testSetSpecificInfo()
    {
        $typeDossierTranslator = $this->getObjectInstancier()->getInstance(TypeDossierTranslator::class);
        $typeDossierData = new TypeDossierProperties();
        $typeDossierData->etape[] = new TypeDossierEtapeProperties();
        $typeDossierData->etape[0]->type = 'sae';
        $result = $typeDossierTranslator->getDefinition($typeDossierData);
        $this->assertArrayNotHasKey('Configuration SAE', $result['page-condition']);
    }

    public function testHasConfigurationSAE()
    {
        $typeDossierTranslator = $this->getObjectInstancier()->getInstance(TypeDossierTranslator::class);
        $typeDossierData = new TypeDossierProperties();
        $typeDossierData->etape[] = new TypeDossierEtapeProperties();
        $typeDossierData->etape[0]->type = 'sae';
        $typeDossierData->etape[0]->specific_type_info['sae_has_metadata_in_json'] = 'On';
        $result = $typeDossierTranslator->getDefinition($typeDossierData);
        $this->assertArrayHasKey('Configuration SAE', $result['page-condition']);
    }
}
