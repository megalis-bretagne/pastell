<?php

namespace Service;

use Exception;
use Pastell\Service\TokenGenerator;
use PHPUnit\Framework\TestCase;

class TokenGeneratorTest extends TestCase
{

    /**
     * @throws Exception
     */
    public function testGenerate()
    {
        $tokenGenerator = new TokenGenerator();
        $this->assertNotEmpty($tokenGenerator->generate());
    }
}
