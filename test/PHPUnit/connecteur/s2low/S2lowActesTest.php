<?php

class S2lowActesTest extends PastellTestCase
{
    /**
     * @throws DonneesFormulaireException
     * @throws S2lowException
     */
    public function testPostActesOK(): void
    {
        $curlWrapper = $this->createMock(CurlWrapper::class);

        $curlWrapper
            ->method('get')
            ->willReturnCallback(function ($url) {
                if ($url === '/modules/actes/actes_classification_fetch.php?api=1') {
                    return file_get_contents(__DIR__ . '/fixtures/classification-exemple.xml');
                }
                if ($url === '/admin/users/api-list-login.php') {
                    return true;
                }
                if ($url === '/modules/actes/actes_transac_create.php') {
                    return "OK\n666";
                }
                throw new \RuntimeException("$url inatendu");
            });

        $addPostDataCall = [];

        $curlWrapper
            ->method('addPostData')
            ->willReturnCallback(
                (static function ($key, $value) use (&$addPostDataCall) {
                    $addPostDataCall[$key] = $value;
                    return true;
                })
            );

        $curlWrapperFactory = $this->createMock(CurlWrapperFactory::class);

        $curlWrapperFactory
            ->method('getInstance')
            ->willReturn($curlWrapper);

        $this->getObjectInstancier()->setInstance(CurlWrapperFactory::class, $curlWrapperFactory);

        $form = $this->getDonneesFormulaireFactory()->getNonPersistingDonneesFormulaire();
        $form->addFileFromCopy(
            'classification_file',
            'classification.xml',
            __DIR__ . '/fixtures/classification-exemple.xml'
        );

        $s2low = new S2low($this->getObjectInstancier());
        $s2low->setConnecteurConfig($form);

        $acte = new TdtActes();
        $acte->acte_nature = '3';
        $acte->numero_de_lacte = '201903251130';
        $acte->objet = 'TEST';
        $acte->date_de_lacte = '2019-03-25';
        $acte->classification = '2.1';
        $acte->arrete = new Fichier();
        $acte->arrete->filepath = __DIR__ . '/fixtures/classification-exemple.xml';
        $acte->arrete->filename = 'test.pdf';

        $annexe = new Fichier();
        $annexe->filepath = __DIR__ . '/fixtures/classification-exemple.xml';
        $annexe->filename = 'annexe1.pdf';

        $acte->autre_document_attache = [$annexe];

        $this->assertSame('666', $s2low->sendActes($acte));

        $this->assertEquals(
            [
                'api' => 1,
                'nature_code' => '3',
                'number' => '201903251130',
                'subject' => 'TEST',
                'decision_date' => '2019-03-25',
                'en_attente' => 0,
                'document_papier' => 0,
                'classif1' => '2',
                'classif2' => '1',
            ],
            $addPostDataCall
        );
    }
}
