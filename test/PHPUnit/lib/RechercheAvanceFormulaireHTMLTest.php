<?php

class RechercheAvanceFormulaireHTMLTest extends PastellTestCase
{
    /**
     * @throws Exception
     */
    public function testWhenHaveExternalDataField()
    {
        $id_ce = $this->createConnector('iParapheur', 'I-Parapheur')['id_ce'];

        $connecteurConfig = $this->getConnecteurFactory()->getConnecteurConfig($id_ce);

        $connecteurConfig->addFileFromData(
            'iparapheur_sous_type',
            'iparapheur_sous_type.tx',
            "Cachet serveur\nCommande\nCourrier"
        );

        $this->associateFluxWithConnector($id_ce, "pdf-generique", "signature");

        $this->getObjectInstancier()->Authentification->Connexion('admin', 1);
        $rechercheAvancerFormulaireHTML  = $this->getObjectInstancier()->getInstance(
            RechercheAvanceFormulaireHTML::class
        );
        $rechercheAvancerFormulaireHTML->setRecuperateur(new Recuperateur(['type' => 'pdf-generique','id_e' => 1]));

        ob_start();
        $rechercheAvancerFormulaireHTML->display();
        $contents = ob_get_contents();
        ob_end_clean();

        $this->assertContains("<select name='iparapheur_sous_type' class=\"form-control col-md-8\">
            <option value=''></option>
                            <option                         value='Cachet serveur'>Cachet serveur</option>
                            <option                         value='Commande'>Commande</option>
                            <option                         value='Courrier'>Courrier</option>", $contents);
    }

    /**
     * @throws Exception
     */
    public function testWhenHaveExternalDataFieldAndNoAssociation()
    {

        $this->getObjectInstancier()->Authentification->Connexion('admin', 1);
        $rechercheAvancerFormulaireHTML  = $this->getObjectInstancier()->getInstance(
            RechercheAvanceFormulaireHTML::class
        );
        $rechercheAvancerFormulaireHTML->setRecuperateur(new Recuperateur(['type' => 'pdf-generique','id_e' => 1]));

        ob_start();
        $rechercheAvancerFormulaireHTML->display();
        $contents = ob_get_contents();
        ob_end_clean();

        $this->assertContains("<select name='iparapheur_sous_type' class=\"form-control col-md-8\">
            <option value=''></option>
                    </select>", $contents);
    }
}
