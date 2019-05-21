<?php

class TdtConnecteurTest extends PastellTestCase
{

    /**
     * @var TdtConnecteur $tdtConnecteur
     */
    private $tdtConnecteur;

    protected function setUp()
    {
        parent::setUp();

        $this->tdtConnecteur = $this->getMockForAbstractClass(TdtConnecteur::class);
    }

    public function getShortenedNatureActeProvider()
    {
        return [
            'DE' => [1, 'DE'],
            'AR' => [2, 'AR'],
            'AI' => [3, 'AI'],
            'CC' => [4, 'CC'],
            'BF' => [5, 'BF'],
            'AU' => [6, 'AU'],
        ];

    }

    /**
     * @dataProvider getShortenedNatureActeProvider
     * @throws Exception
     */
    public function testGetShortenedNatureActe($natureActe, $expectedValue)
    {
        $this->assertSame(
            $expectedValue,
            $this->tdtConnecteur->getShortenedNatureActe($natureActe)
        );
    }

    /**
     * @throws Exception
     */
    public function testGetShortenedNatureActeException()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("La nature 8 est inconnue.");
        $this->tdtConnecteur->getShortenedNatureActe(8);
    }
}

