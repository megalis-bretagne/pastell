<?php

class S2lowGlobalTest extends ControlerTestCase
{
    /**
     * @throws Exception
     */
    public function testModifCertificat()
    {

        $result = $this->getInternalAPI()->post(
            "/entite/1/connecteur",
            ['libelle' => 'S2low' , 'id_connecteur' => 's2low']
        );

        $id_ce_entite = $result['id_ce'];

        $result = $this->getInternalAPI()->post(
            "/entite/0/connecteur",
            ['libelle' => 'S2low' , 'id_connecteur' => 's2low']
        );

        $id_ce = $result['id_ce'];

        $connecteurControler = $this->getControlerInstance(ConnecteurControler::class);

        $this->setGetInfo(['id_ce' => $id_ce,'field' => 'changement_certificat']);

        $this->expectOutputRegex("#input type='checkbox' name='id_ce_list\[\]' value='$id_ce_entite'#");
        $connecteurControler->externalDataAction();
    }


    /**
     * @throws Exception
     */
    public function testDoModifCertificat()
    {

        $result = $this->getInternalAPI()->post(
            "/entite/1/connecteur",
            ['libelle' => 'S2low' , 'id_connecteur' => 's2low']
        );

        $id_ce_entite = $result['id_ce'];

        $result = $this->getInternalAPI()->post(
            "/entite/0/connecteur",
            ['libelle' => 'S2low' , 'id_connecteur' => 's2low']
        );

        $id_ce = $result['id_ce'];

        /** @var ConnecteurControler $connecteurControler */
        $connecteurControler = $this->getControlerInstance(ConnecteurControler::class);

        $this->setGetInfo(['id_ce' => $id_ce,'field' => 'changement_certificat','id_ce_list' => [$id_ce_entite]]);

        $this->expectOutputRegex("#Location: " . SITE_BASE . "(.*)editionModif\?id_ce=$id_ce#");
        $connecteurControler->doExternalDataAction();
    }
}
