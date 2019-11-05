<?php

class ModificationActionTest extends PastellTestCase
{

    /**
     * @throws NotFoundException
     */
    public function testNoEditableContent()
    {
        $document = $this->createDocument('test');

        $this->configureDocument($document['id_d'], [
            'test2' => 'test required field'
        ]);

        $donnesFormulaire = $this->getDonneesFormulaireFactory()->get($document['id_d']);
        $this->assertSame('test required field', $donnesFormulaire->get('test2'));

        $this->triggerActionOnDocument($document['id_d'], 'useless');

        $this->configureDocument($document['id_d'], [
            'test2' => 'test new value'
        ]);
        $donnesFormulaire = $this->getDonneesFormulaireFactory()->get($document['id_d']);
        $this->assertSame('test required field', $donnesFormulaire->get('test2'));
    }

    public function testDontRedirectOnAPICall()
    {
        $document = $this->createDocument('helios-generique');
        $this->expectOutputString("");
        $result = $this->getInternalAPI()->patch("/Entite/1/Document/{$document['id_d']}", ['envoi_sae' => 1]);
        $this->assertEquals(1, $result['content']['data']['envoi_sae']);
    }
}
