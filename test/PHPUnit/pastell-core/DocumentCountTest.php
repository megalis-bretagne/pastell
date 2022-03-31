<?php

class DocumentCountTest extends PastellTestCase
{
    public function testCountAll()
    {
        $this->getInternalAPI()->post("/entite/1/document", ['type' => 'actes-generique']);
        $documentCount = $this->getObjectInstancier()->getInstance(DocumentCount::class);
        $result = $documentCount->getAll(1);
        $this->assertEquals(
            [
                1 =>
                     [
                        'flux' =>
                             [
                                'actes-automatique' =>
                                     [],
                                'actes-generique' =>
                                     [
                                        'creation' => '1',
                                    ],
                                'actes-preversement-seda' =>
                                     [],
                                'helios-automatique' =>
                                     [],
                                'helios-generique' =>
                                     [],
                                'pdf-generique' =>
                                     [],
                                'mailsec' =>
                                     [],
                                'test' =>
                                     [],
                                'document-a-signer' => [],
                                'actes-reponse-prefecture' => [],
                                'commande-generique' => [],
                                'mailsec-bidir' => []
                            ],
                        'info' =>
                             [
                                'id_e' => '1',
                                'type' => 'collectivite',
                                'denomination' => 'Bourg-en-Bresse',
                                'siren' => '123456789',
                                'date_inscription' => '0000-00-00 00:00:00',
                                'etat' => '0',
                                'entite_mere' => '0',
                                'centre_de_gestion' => '0',
                                'is_active' => '1',
                            ],
                    ],
                2 =>
                     [
                        'flux' =>
                             [
                                'actes-automatique' =>
                                     [],
                                'actes-generique' =>
                                     [],
                                'actes-preversement-seda' =>
                                     [],
                                'helios-automatique' =>
                                     [],
                                'helios-generique' =>
                                     [],
                                'pdf-generique' =>
                                     [],
                                'mailsec' =>
                                     [],
                                'test' =>
                                     [],
                                'document-a-signer' => [],
                                'actes-reponse-prefecture' => [],
                                'commande-generique' => [],
                                'mailsec-bidir' => []
                            ],
                        'info' =>
                             [
                                'id_e' => '2',
                                'type' => 'service',
                                'denomination' => 'CCAS',
                                'siren' => '123456788',
                                'date_inscription' => '0000-00-00 00:00:00',
                                'etat' => '0',
                                'entite_mere' => '1',
                                'centre_de_gestion' => '0',
                                'is_active' => '1',
                            ],
                    ],
             ],
            $result
        );
    }

    public function testCountLimit()
    {
        $this->getInternalAPI()->post("/entite/1/document", ['type' => 'actes-generique']);
        $documentCount = $this->getObjectInstancier()->getInstance(DocumentCount::class);
        $result = $documentCount->getAll(1, 1, 'actes-generique');
        $this->assertEquals(
            [
                1 =>
                     [
                        'flux' =>
                             [
                                'actes-generique' =>
                                     [
                                        'creation' => '1',
                                    ],
                            ],
                        'info' =>
                             [
                                'id_e' => '1',
                                'type' => 'collectivite',
                                'denomination' => 'Bourg-en-Bresse',
                                'siren' => '123456789',
                                'date_inscription' => '0000-00-00 00:00:00',
                                'etat' => '0',
                                'entite_mere' => '0',
                                'centre_de_gestion' => '0',
                                'is_active' => '1',
                            ],
                    ]
             ],
            $result
        );
    }
}
