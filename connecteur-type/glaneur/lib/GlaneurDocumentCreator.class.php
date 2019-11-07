<?php

require_once __DIR__ . "/GlaneurDocumentInfo.class.php";

class GlaneurDocumentCreator
{

    private $document;
    private $documentEntite;
    private $actionCreatorSQL;
    private $donneesFormulaireFactory;
    private $actionExecutorFactory;

    private $jobManager;
    private $documentCreationService;

    public function __construct(
        DocumentSQL $document,
        DocumentEntite $documentEntite,
        ActionCreatorSQL $actionCreatorSQL,
        DonneesFormulaireFactory $donneesFormulaireFactory,
        ActionExecutorFactory $actionExecutorFactory,
        JobManager $jobManager,
        DocumentCreationService $documentCreationService
    ) {
        $this->document = $document;
        $this->documentEntite = $documentEntite;
        $this->actionCreatorSQL = $actionCreatorSQL;
        $this->donneesFormulaireFactory = $donneesFormulaireFactory;
        $this->actionExecutorFactory = $actionExecutorFactory;
        $this->jobManager = $jobManager;
        $this->documentCreationService = $documentCreationService;
    }

    /**
     * @param GlaneurDocumentInfo $glaneurLocalDocumentInfo
     * @return string
     * @throws Exception
     */
    public function create(GlaneurDocumentInfo $glaneurLocalDocumentInfo, string $repertoire)
    {

        $new_id_d = $this->documentCreationService->createDocumentWithoutAuthorizationChecking(
            $glaneurLocalDocumentInfo->id_e,
            $glaneurLocalDocumentInfo->nom_flux
        );

        $donneesFormulaire = $this->donneesFormulaireFactory->get($new_id_d);

        foreach ($glaneurLocalDocumentInfo->metadata as $key => $value) {
            $donneesFormulaire->setData($key, $value);
        }

        $titre_fieldname = $donneesFormulaire->getFormulaire()->getTitreField();
        $titre = $donneesFormulaire->get($titre_fieldname);
        $this->document->setTitre($new_id_d, $titre);

        foreach ($glaneurLocalDocumentInfo->element_files_association as $key => $files_list) {
            foreach ($files_list as $file_num => $file) {
                $donneesFormulaire->addFileFromCopy($key, $file, $repertoire . "/" . $file, $file_num);
            }
        }

        $this->actionCreatorSQL->addAction(
            $glaneurLocalDocumentInfo->id_e,
            0,
            Action::CREATION,
            "[glaneur] Import du document",
            $new_id_d
        );

        if (! $glaneurLocalDocumentInfo->action_ok) {
            return $new_id_d;
        }

        // A ce stade, l'import est réussi, si le document est ko alors il passe dans un etat d'erreur, mais on le supprime
        if ($donneesFormulaire->isValidable()) {
            $message = "[glaneur] Passage en action_ok : {$glaneurLocalDocumentInfo->action_ok}";
            $next_state = $glaneurLocalDocumentInfo->action_ok;
        } elseif ($glaneurLocalDocumentInfo->force_action_ok) {
            $message = "[glaneur] Passage en action_ok forcé : {$glaneurLocalDocumentInfo->action_ok}";
            $next_state = $glaneurLocalDocumentInfo->action_ok;
        } else {
            $message = "[glaneur] Le dossier n'est pas valide : " . $donneesFormulaire->getLastError();
            $next_state = $glaneurLocalDocumentInfo->action_ko ?: "fatal-error";
        }
        $this->actionCreatorSQL->addAction(
            $glaneurLocalDocumentInfo->id_e,
            0,
            $next_state,
            $message,
            $new_id_d
        );
        $this->jobManager->setJobForDocument($glaneurLocalDocumentInfo->id_e, $new_id_d, $message);
        return $new_id_d;
    }
}
