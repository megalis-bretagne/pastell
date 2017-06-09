<?php

class HeliosGeneriqueDefintionFileTest extends PastellTestCase {

    public function testDefinitionFile(){
        /** @var SystemControler $systemControler */
        $systemControler = $this->getObjectInstancier()->getInstance('SystemControler');
        $this->assertTrue($systemControler->isDocumentTypeValid('helios-generique'));
    }



}