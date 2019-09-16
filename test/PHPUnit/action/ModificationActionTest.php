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
}