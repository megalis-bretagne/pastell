<?php

use PHPUnit\Framework\TestCase;

class ObjectInstancierTest extends TestCase
{

    public function testOptionalParameter()
    {
        $objectInstancier = new ObjectInstancier();
        $class = new class {
            private $test;
            public function __construct($thisVarDoesntExistAnywhere_48b2ee87031d176b368c0d31db167352 = 'default')
            {
                $this->test = $thisVarDoesntExistAnywhere_48b2ee87031d176b368c0d31db167352;
            }
        };

        $object = $objectInstancier->getInstance(get_class($class));

        $this->assertInstanceOf(get_class($class), $object);
    }
}
