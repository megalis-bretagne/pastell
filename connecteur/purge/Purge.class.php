<?php

class Purge extends Connecteur {

	const GO_TROUGH_STATE = "GO_TROUGH_STATE";
	const IN_STATE= "IN_STATE";


    /** @var  DonneesFormulaire */
    private $connecteurConfig;

    /** @var DocumentActionEntite  */
    private $documentActionEntite;

    private $journal;
    private $jobManager;

    private $lastMessage;

    private $actionPossible;


    public function __construct(
    	DocumentActionEntite $documentActionEntite,
		Journal $journal,
		JobManager $jobManager,
		ActionPossible $actionPossible
	){
        $this->documentActionEntite = $documentActionEntite;
        $this->journal = $journal;
        $this->jobManager = $jobManager;
		$this->actionPossible = $actionPossible;
    }

    public function getLastMessage(){
    	return $this->lastMessage;
	}

    public function isActif(){
        return (bool) $this->connecteurConfig->get('actif');
    }

    public function setConnecteurConfig(DonneesFormulaire $donneesFormulaire) {
        $this->connecteurConfig = $donneesFormulaire;
    }

    public function listDocument(){
        $connecteur_info  =$this->getConnecteurInfo();

		$passer_par_letat = $this->connecteurConfig->get('passer_par_l_etat');
        if ($passer_par_letat == self::GO_TROUGH_STATE) {
			return $this->documentActionEntite->getDocumentInStateOlderThanDay(
				$connecteur_info['id_e'],
				$this->connecteurConfig->get('document_type'),
				$this->connecteurConfig->get('document_etat'),
				$this->connecteurConfig->get('nb_days')
			);
		} else {
			return $this->documentActionEntite->getDocumentOlderThanDay(
				$connecteur_info['id_e'],
				$this->connecteurConfig->get('document_type'),
				$this->connecteurConfig->get('document_etat'),
				$this->connecteurConfig->get('nb_days')
			);
		}

    }

	/**
	 * @return bool
	 * @throws UnrecoverableException
	 */
	public function purger(){
		if (! $this->isActif()){
			throw new UnrecoverableException("Le connecteur n'est pas actif");
		}
		$document_list = $this->listDocument();

		$connecteur_info  = $this->getConnecteurInfo();

		$etat_cible = $this->connecteurConfig->get('document_etat_cible')?:'supression';


		$this->lastMessage = "Programmation de la purge des documents : ";
		foreach($document_list as $document_info) {

			if (! $this->actionPossible->isActionPossible($document_info['id_e'],0,$document_info['id_d'],$etat_cible)){
				$this->lastMessage.= get_hecho("{$document_info['id_d']} - {$document_info['titre']} - {$document_info['last_action_date']}") . " : action impossible : ".$this->actionPossible->getLastBadRule()."<br/>";
				continue;
			}

			$this->journal->add(
				Journal::DOCUMENT_TRAITEMENT_LOT,
				$document_info['id_e'],
				$document_info['id_d'],$etat_cible,
				"Programmation dans le cadre du connecteur de purge {$connecteur_info['id_ce']}");


			$this->jobManager->setTraitementLot(
				$document_info['id_e'],
				$document_info['id_d'],
				0,
				$etat_cible
			);
			$this->lastMessage .= get_hecho("{$document_info['id_d']} - {$document_info['titre']} - {$document_info['last_action_date']}") . "<br/>";
		}

		return true;
	}


}