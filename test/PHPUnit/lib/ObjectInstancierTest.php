<?php

use PHPUnit\Framework\TestCase;

class ObjectInstancierTest extends TestCase
{

    public function testGetObjectWithOptionalParameter()
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

    public function testGetObjectWithUnknownRequiredParameter()
    {
        $objectInstancier = new ObjectInstancier();
        $class = new class (10) {
            private $test;
            public function __construct($thisVarDoesntExistAnywhere_f45c6927364839acc8dbe0a299c971aa)
            {
                $this->test = $thisVarDoesntExistAnywhere_f45c6927364839acc8dbe0a299c971aa;
            }
        };

        $this->expectException(Exception::class);
        $objectInstancier->getInstance(get_class($class));
    }

    public function testGetObjectWithKnownOptionalParameter()
    {
        $objectInstancier = new ObjectInstancier();
        $class = new class (10) {
            public $test;
            public function __construct($optionalParameter = 'optional value')
            {
                $this->test = $optionalParameter;
            }
        };

        $objectInstancier->setInstance('optionalParameter', 'this value is known');

        $object = $objectInstancier->getInstance(get_class($class));

        $this->assertSame('this value is known', $object->test);
    }
}
