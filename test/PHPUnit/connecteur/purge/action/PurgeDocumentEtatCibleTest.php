<?php

class PurgeDocumentEtatCibleTest extends PastellTestCase
{
    /**
     * @throws Exception
     */
    public function testApi()
    {
        $result = $this->getInternalAPI()->post(
            "/entite/" . self::ID_E_COL . "/connecteur",
            array('libelle' => 'purge' , 'id_connecteur' => 'purge')
        );

        $id_ce = $result['id_ce'];
        $this->getInternalAPI()->patch(
            "/entite/" . self::ID_E_COL . "/connecteur/$id_ce/content/  ",
            array(
                'document_type' => 'actes-generique',
                'document_etat' => 'accepter-sae',
            )
        );

        $purgeDocumentEtatCible = new PurgeDocumentEtatCible($this->getObjectInstancier());
        $purgeDocumentEtatCible->setConnecteurId('purge', $id_ce);

        $result = $purgeDocumentEtatCible->displayAPI();

        $this->assertEquals("Créé", $result['creation']['name']);
    }
}
