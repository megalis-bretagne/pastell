<?php

/**
 * Class LegacyPHPUnit_Framework_TestCase
 * Classe crée lors du passage de PHPUnit 4.7 vers 6.4
 * @deprecated
 */
class LegacyPHPUnit_Framework_TestCase extends PHPUnit\Framework\TestCase {

    /**
     * @deprecated
     */
    public function thisTestDidNotPerformAnyAssertions(){
        $this->addToAssertionCount(1);
    }

}