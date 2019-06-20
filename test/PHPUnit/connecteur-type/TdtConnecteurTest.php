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

    public function getStatusInfoProvider()
    {
        return [
            [-1, "Erreur"],
            [0, "Annulé"],
            [1, "Posté"],
            [2, "En attente de transmission. Fichier valide."],
            [3, "Transmis"],
            [4, "Acquittement reçu"],
            [5, "status 5 invalide"],
            [6, "Refusé"],
            [7, "En traitement"],
            [8, "Information disponible"],
            [9, "Status 9 inconnu sur Pastell"],
        ];
    }

    /**
     * @dataProvider getStatusInfoProvider
     * @throws Exception
     */
    public function testGetStatusInfo($status, $expectedValue)
    {
        $this->assertSame(
            $expectedValue,
            $this->tdtConnecteur->getStatusInfo($status)
        );
    }

    public function getStatusStringProvider()
    {
        return [
            [-1, 'Erreur'],
            [0, 'Annulé'],
            [1, 'Posté'],
            [2, 'En attente de transmission'],
            [3, 'Transmis'],
            [4, 'Acquittement reçu'],
            [5, 'Validé'],
            [6, 'Refusé'],
            [7, 'AR non disponible pour le moment'],
            [17, "En attente d'être postée"],
            [18, "Statut inconnu (18)"],
        ];
    }

    /**
     * @dataProvider getStatusStringProvider
     * @throws Exception
     */
    public function testGetStatusString($status, $expectedValue) {
        $this->assertSame(
            $expectedValue,
            $this->tdtConnecteur::getStatusString($status)
        );
    }
}

