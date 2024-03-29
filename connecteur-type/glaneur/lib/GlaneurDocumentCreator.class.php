<?php

require_once __DIR__."/GlaneurDocumentInfo.class.php";

class GlaneurDocumentCreator {

    private $document;
    private $documentEntite;
    private $actionCreatorSQL;
    private $donneesFormulaireFactory;
    private $actionExecutorFactory;

    private $jobManager;

    public function __construct(
        Document $document,
        DocumentEntite $documentEntite,
        ActionCreatorSQL $actionCreatorSQL,
        DonneesFormulaireFactory $donneesFormulaireFactory,
        ActionExecutorFactory $actionExecutorFactory,
        JobManager $jobManager
    ) {
        $this->document = $document;
        $this->documentEntite = $documentEntite;
        $this->actionCreatorSQL = $actionCreatorSQL;
        $this->donneesFormulaireFactory = $donneesFormulaireFactory;
        $this->actionExecutorFactory = $actionExecutorFactory;
        $this->jobManager = $jobManager;
    }

    /**
     * @param GlaneurDocumentInfo $glaneurLocalDocumentInfo
     * @return string
     * @throws Exception
     */
    public function create(GlaneurDocumentInfo $glaneurLocalDocumentInfo, string $repertoire){
        $new_id_d = $this->document->getNewId();
        $this->document->save(
            $new_id_d,
            $glaneurLocalDocumentInfo->nom_flux
        );
        $this->documentEntite->addRole(
            $new_id_d,
            $glaneurLocalDocumentInfo->id_e,
            "editeur"
        );

        $donneesFormulaire = $this->donneesFormulaireFactory->get($new_id_d);

        foreach($glaneurLocalDocumentInfo->metadata as $key => $value){
            $donneesFormulaire->setData($key,$value);
        }

        $titre_fieldname = $donneesFormulaire->getFormulaire()->getTitreField();
        $titre = $donneesFormulaire->get($titre_fieldname);
        $this->document->setTitre($new_id_d,$titre);

        foreach($glaneurLocalDocumentInfo->element_files_association as $key => $files_list){
            foreach($files_list as $file_num => $file){
                $donneesFormulaire->addFileFromCopy($key,$file,$repertoire."/".$file,$file_num);
            }
        }

        $this->actionCreatorSQL->addAction(
            $glaneurLocalDocumentInfo->id_e,
            0,
            Action::CREATION,
            "[glaneur] Import du document",
            $new_id_d
        );

        if (! $glaneurLocalDocumentInfo->action_ok){
            return $new_id_d;
        }

        // A ce stade, l'import est réussi, si le document est ko alors il passe dans un etat d'erreur, mais on le supprime
        if ($donneesFormulaire->isValidable()){
			$message = "[glaneur] Passage en action_ok : {$glaneurLocalDocumentInfo->action_ok}";
			$next_state = $glaneurLocalDocumentInfo->action_ok;
        } else {
			$message = "[glaneur] Le document n'est pas valide : " . $donneesFormulaire->getLastError();
			$next_state = $glaneurLocalDocumentInfo->action_ko?:"fatal-error";
		}
		$this->actionCreatorSQL->addAction(
			$glaneurLocalDocumentInfo->id_e,
			0,
			$next_state,
			$message,
			$new_id_d
		);
        $this->jobManager->setJobForDocument($glaneurLocalDocumentInfo->id_e, $new_id_d,$message);
        return $new_id_d;
    }

}