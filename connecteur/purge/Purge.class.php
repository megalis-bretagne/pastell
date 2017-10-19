<?php

class Purge extends Connecteur {

    /** @var  DonneesFormulaire */
    private $connecteurConfig;

    /** @var DocumentActionEntite  */
    private $documentActionEntite;

    public function __construct(DocumentActionEntite $documentActionEntite){
        $this->documentActionEntite = $documentActionEntite;
    }

    public function isActif(){
        return (bool) $this->connecteurConfig->get('actif');
    }

    public function setConnecteurConfig(DonneesFormulaire $donneesFormulaire) {
        $this->connecteurConfig = $donneesFormulaire;
    }

    public function listDocument(){
        $connecteur_info  =$this->getConnecteurInfo();

        return $this->documentActionEntite->getDocumentOlderThanDay(
            $connecteur_info['id_e'],
            $this->connecteurConfig->get('document_type'),
            $this->connecteurConfig->get('document_etat'),
            $this->connecteurConfig->get('nb_days')
        );

    }


}