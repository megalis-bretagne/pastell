<?php

class RecuperationFichierLocalTestTest extends PastellTestCase {

    private $id_ce;

    protected function setUp() {
        parent::setUp();
        $result = $this->getInternalAPI()->post(
            "/Entite/1/Connecteur/",
            array(
                'id_connecteur' => 'recuperation-fichier-local',
                'libelle' => 'Récupération fichier local'
            )
        );
        $this->id_ce = $result['id_ce'];
    }

    public function testConnexion(){
        $this->getInternalAPI()->patch(
          "/Entite/1/Connecteur/{$this->id_ce}/content",
          array(
              'directory'=> '/tmp/',
              'directory_send' => '/tmp/'
          )
        );

        $result = $this->getInternalAPI()->post(
            "/Entite/1/Connecteur/{$this->id_ce}/action/test"
        );
        $this->assertRegExp(
            "#Lecture du répertoire OK.#",
            $result['last_message']
        );
    }

    public function testConnexionWithoutDirectorySend(){
        $this->getInternalAPI()->patch(
            "/Entite/1/Connecteur/{$this->id_ce}/content",
            array(
                'directory'=> '/tmp/',
            )
        );

        $result = $this->getInternalAPI()->post(
            "/Entite/1/Connecteur/{$this->id_ce}/action/test"
        );
        $this->assertRegExp(
            "#Lecture du répertoire OK.#",
            $result['last_message']
        );
    }
}