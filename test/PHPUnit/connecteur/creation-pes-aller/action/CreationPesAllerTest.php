<?php

class CreationPesAllerTest extends PastellTestCase {

    /*
     * Lorsqu'on change le libellé du connecteur de récupération, alors il faut
     * que les libellés affichés dans les connecteurs de création de document
     * soit mis à jour.
     *
     * En version 2.0, c'est pas possible simplement car il n'y a pas de hook sur la modification
     * d'un libellé.
     *
     *
     */
    public function testChoix(){

        $connecteur_recup_orig_name = 'Récup local';
        $connecteur_recup_new_name = 'Nouveau nom';

        $info = $this->getInternalAPI()->post("/Entite/1/Connecteur",
            array(
                'libelle'=>$connecteur_recup_orig_name,
                'id_connecteur' => 'recuperation-fichier-local'
            )
        );
        $id_ce_recup = $info['id_ce'];

        $info = $this->getInternalAPI()->post("/Entite/1/Connecteur",
            array(
                'libelle' => 'Création document pes aller',
                'id_connecteur' => 'creation-pes-aller'
            )
        );
        $id_ce_creation = $info['id_ce'];

        $result = $this->getInternalAPI()->get(
            "/Entite/1/Connecteur/$id_ce_creation/externalData/connecteur_recup"
        );

        $this->assertEquals($connecteur_recup_orig_name,$result[$id_ce_recup]);

        $info = $this->getInternalAPI()->patch(
            "/Entite/1/Connecteur/$id_ce_creation/externalData/connecteur_recup",
            array('connecteur_recup'=>$id_ce_recup)
        );

        $this->assertEquals($connecteur_recup_orig_name, $info['data']['connecteur_recup']);
        $this->assertEquals($id_ce_recup, $info['data']['connecteur_recup_id']);


        $result = $this->getInternalAPI()->patch(
            "/Entite/1/Connecteur/$id_ce_recup",
            array(
                'libelle' => $connecteur_recup_new_name
            )
        );
        $this->assertEquals($connecteur_recup_new_name,$result['libelle']);

        $result = $this->getInternalAPI()->get("/Entite/1/Connecteur/$id_ce_creation");
        $this->assertEquals($connecteur_recup_orig_name, $result['data']['connecteur_recup']);
        //$this->assertEquals($connecteur_recup_new_name, $result['data']['connecteur_recup']);
    }


}