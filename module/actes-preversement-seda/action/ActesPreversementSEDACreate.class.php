<?php

class ActesPreversementSEDACreate extends ActionExecutor {

    const FLUX_NAME = 'actes-automatique';
    const ACTES_NAMESPACE = "http://www.interieur.gouv.fr/ACTES#v1.1-20040216";

    /**
     * @throws Exception
     */
    public function go(){

        $enveloppe_metier = $this->getDonneesFormulaire()->getFilePath('enveloppe_metier');

        $simpleXMLWrapper = new SimpleXMLWrapper();

        $xml = $simpleXMLWrapper->loadFile($enveloppe_metier);

        $attributes = $xml->attributes(self::ACTES_NAMESPACE);

        $code_nature = $this->retrieveAttributes($attributes,'CodeNatureActe');
        $numero_interne = $this->retrieveAttributes($attributes,'NumeroInterne');
        $numero_interne = preg_replace("#-#","_",$numero_interne);
		$numero_interne = strtoupper($numero_interne);
        $date = $this->retrieveAttributes($attributes,'Date');

        $children = $xml->children(self::ACTES_NAMESPACE);

        $objet = $this->retrieveChildrenContent($children,'Objet');

        $code_matiere = [];
        for($i = 1; $i<=5; $i++){
            if (isset($children->{"CodeMatiere$i"}['CodeMatiere'])){
                $code_matiere[]= strval($children->{"CodeMatiere$i"}['CodeMatiere']);
            }
        }
        $code_matiere = implode('.',$code_matiere);

        $documentCreationService = $this->objectInstancier->getInstance(DocumentCreationService::class);
        $new_id_d = $documentCreationService->createDocument($this->id_e,$this->id_u,self::FLUX_NAME);

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($new_id_d);
        $donneesFormulaire->setData('acte_nature',$code_nature);
        $donneesFormulaire->setData('numero_de_lacte',$numero_interne);
        $donneesFormulaire->setData('objet',$objet);
        $donneesFormulaire->setData('date_de_lacte',$date);
        $donneesFormulaire->setData('classification',$code_matiere);
		$donneesFormulaire->setData('type_piece',"Type de pièce non positionné");

		$donneesFormulaire->addFileFromData("type_piece_fichier","type_piece.json","[]");

        $donneesFormulaire->addFileFromCopy(
            'arrete',
            $this->getDonneesFormulaire()->getFileName('document',0),
            $this->getDonneesFormulaire()->getFilePath('document',0)
        );


        $annexes_list = $this->getDonneesFormulaire()->get('document');
        array_shift($annexes_list);
        foreach($annexes_list as $i => $annexe){
            $donneesFormulaire->addFileFromCopy(
                'autre_document_attache',
                $this->getDonneesFormulaire()->getFileName('document',$i+1),
                $this->getDonneesFormulaire()->getFilePath('document',$i+1),
                $i
            );
        }
        $donneesFormulaire->setData('envoi_sae',true);
        $donneesFormulaire->setData('has_information_complementaire',true);

        $donneesFormulaire->addFileFromCopy(
            'aractes',
            $this->getDonneesFormulaire()->getFileName('aractes',0),
            $this->getDonneesFormulaire()->getFilePath('aractes',0)
        );

        $titre_fieldname = $donneesFormulaire->getFormulaire()->getTitreField();
        $titre = $donneesFormulaire->get($titre_fieldname);
        $this->getDocument()->setTitre($new_id_d,$titre);

        if (! $donneesFormulaire->isValidable()){
            $message = "Le document $new_id_d créé n'est pas valide : ".$donneesFormulaire->getLastError();
            $this->changeAction('erreur',$message);
            throw new Exception($message);
        }

        $message = "[actes-preversement-seda] Passage en importation";
        $this->getActionCreator($new_id_d)->addAction(
            $this->id_e,
            0,
            'importation',
            $message
        );
        $this->getJobManager()->setJobForDocument($this->id_e, $new_id_d,$message);
        $this->addActionOK("Création du document Pastell $new_id_d");
        return true;
    }

    public function getJobManager(){
        return $this->objectInstancier->getInstance('JobManager');
    }


    /**
     * @param SimpleXMLElement $attributes
     * @param $name
     * @return string
     * @throws Exception
     */
    private function retrieveAttributes(SimpleXMLElement $attributes,$name){
        if (empty($attributes->$name)){
            throw new Exception("Impossible de trouver l'attribut $name dans le fichier XML");
        }
        return strval($attributes->$name);
    }

    /**
     * @param SimpleXMLElement $xml
     * @param $child_tag
     * @return string
     * @throws Exception
     */
    public function retrieveChildrenContent(SimpleXMLElement $xml,$child_tag){
        if (empty($xml->$child_tag)){
            throw new Exception("Impossible de trouver l'élement $child_tag");
        }
        return strval($xml->$child_tag);
    }


}