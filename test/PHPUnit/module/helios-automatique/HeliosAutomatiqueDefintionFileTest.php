<?php

class HeliosAutomatiqueDefintionFileTest extends PastellTestCase {

    public function testDefinitionFile(){
        /** @var SystemControler $systemControler */
        $systemControler = $this->getObjectInstancier()->getInstance('SystemControler');
        $this->assertTrue($systemControler->isDocumentTypeValid('helios-automatique'));
    }



}