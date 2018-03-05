<?php

class PurgePurger extends ActionExecutor {



    public function go(){
        /** @var Purge $connecteur */
        $connecteur = $this->getMyConnecteur();

        if (! $connecteur->isActif()){
            $this->setLastMessage("Le connecteur n'est pas actif");
            return true;
        }
        $document_list = $connecteur->listDocument();
        $message = "Programmation de la purge des documents : ";
        foreach($document_list as $document_info) {

            $this->objectInstancier->Journal->add(
                Journal::DOCUMENT_TRAITEMENT_LOT,
                $this->id_e,
                $document_info['id_d'],'supression',
                "Programmation dans le cadre du connecteur de purge {$this->id_ce}");


            $this->objectInstancier->getInstance(JobManager::class)->setTraitementLot(
                $this->id_e,
                $document_info['id_d'],
                $this->id_u,
                'supression'
            );
            $message .= get_hecho("{$document_info['id_d']} - {$document_info['titre']} - {$document_info['last_action_date']}") . "<br/>";
        }

        return true;
    }
}