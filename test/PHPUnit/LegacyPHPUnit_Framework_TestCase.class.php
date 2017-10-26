<?php

/**
 * Class LegacyPHPUnit_Framework_TestCase
 * Classe crée lors du passage de PHPUnit 4.7 vers 6.4
 * @deprecated
 */
class LegacyPHPUnit_Framework_TestCase extends PHPUnit\Framework\TestCase {
    /**
     * @deprecated see https://thephp.cc/news/2016/02/questioning-phpunit-best-practices
     * @param $exception
     * @param $message
     */
    public function setExpectedException($exception,$message = ''){
        $this->expectException($exception);
        if ($message) {
            $this->expectExceptionMessage($message);
        }
    }

    /**
     * @deprecated
     */
    public function thisTestDidNotPerformAnyAssertions(){
        $this->addToAssertionCount(1);
    }

}