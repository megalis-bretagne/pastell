<?php

namespace Pastell\Service\Connecteur;

use ConnecteurEntiteSQL;
use DonneesFormulaireFactory;
use ActionExecutorFactory;
use Exception;
use FileUploader;
use Recuperateur;
use Pastell\Service\Connecteur\ConnecteurActionService;

class ConnecteurModificationService
{
    private $connecteurEntiteSQL;
    private $donneesFormulaireFactory;
    private $connecteurActionService;
    private $actionExecutorFactory;

    private $lastMessage = '';

    public function __construct(
        ConnecteurEntiteSQL $connecteurEntiteSQL,
        DonneesFormulaireFactory $donneesFormulaireFactory,
        ActionExecutorFactory $actionExecutorFactory,
        ConnecteurActionService $connecteurActionService
    ) {
        $this->connecteurEntiteSQL = $connecteurEntiteSQL;
        $this->donneesFormulaireFactory = $donneesFormulaireFactory;
        $this->actionExecutorFactory = $actionExecutorFactory;
        $this->connecteurActionService = $connecteurActionService;
    }

    public function getLastMessage(): string
    {
        return $this->lastMessage;
    }

    public function setLastMessage(string $message)
    {
        $this->lastMessage = $message;
    }

    /**
     * @throws Exception
     */
    public function editConnecteurLibelle(
        int $id_ce,
        string $libelle,
        int $frequence_en_minute = 1,
        string $id_verrou = '',
        int $id_e = 0,
        int $id_u = 0,
        string $message = ''
    ): void {

        $this->connecteurEntiteSQL->edit($id_ce, $libelle, $frequence_en_minute, $id_verrou);

        $this->connecteurActionService->add(
            $id_e,
            $id_u,
            $id_ce,
            '',
            ConnecteurActionService::ACTION_MODIFFIE,
            $message
        );
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function editConnecteurFormulaire(
        int $id_ce,
        Recuperateur $recuperateur,
        FileUploader $fileUploader,
        bool $from_api = false,
        int $id_e = 0,
        int $id_u = 0,
        string $message = ''
    ): bool {

        $result = true;

        $donneesFormulaire = $this->donneesFormulaireFactory->getConnecteurEntiteFormulaire($id_ce);
        if (! $from_api) {
            $donneesFormulaire->saveTab($recuperateur, $fileUploader, 0);
        } else {
            $donneesFormulaire->setTabDataVerif($recuperateur->getAll());
            $donneesFormulaire->saveAllFile($fileUploader);
        }

        foreach ($donneesFormulaire->getOnChangeAction() as $action) {
            $result = $this->actionExecutorFactory->executeOnConnecteur($id_ce, $id_u, $action, $from_api);
            if (! $result) {
                $this->setLastMessage($this->actionExecutorFactory->getLastMessage());
            }
        }

        $this->connecteurActionService->add(
            $id_e,
            $id_u,
            $id_ce,
            '',
            ConnecteurActionService::ACTION_MODIFFIE,
            $message
        );

        return $result;
    }
}
