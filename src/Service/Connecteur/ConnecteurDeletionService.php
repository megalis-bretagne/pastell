<?php

namespace Pastell\Service\Connecteur;

use ConnecteurEntiteSQL;
use DonneesFormulaireFactory;
use Exception;
use FluxEntiteSQL;
use JobManager;

class ConnecteurDeletionService
{
    private $connecteurEntiteSQL;
    private $connecteurActionService;
    private $donneesFormulaireFactory;
    private $fluxEntiteSQL;
    private $jobManager;

    public function __construct(
        ConnecteurEntiteSQL $connecteurEntiteSQL,
        ConnecteurActionService $connecteurActionService,
        DonneesFormulaireFactory $donneesFormulaireFactory,
        FluxEntiteSQL $fluxEntiteSQL,
        JobManager $jobManager
    ) {
        $this->connecteurEntiteSQL = $connecteurEntiteSQL;
        $this->connecteurActionService = $connecteurActionService;
        $this->donneesFormulaireFactory = $donneesFormulaireFactory;
        $this->fluxEntiteSQL = $fluxEntiteSQL;
        $this->jobManager = $jobManager;
    }

    /**
     * @throws Exception
     */
    public function deleteConnecteur(int $id_ce): void
    {
        $id_used = $this->fluxEntiteSQL->getFluxByConnecteur($id_ce);
        if ($id_used) {
            throw new Exception("Ce connecteur est utilisé par des flux :  " . implode(", ", $id_used));
        }
        $this->donneesFormulaireFactory->getConnecteurEntiteFormulaire($id_ce)->delete();
        $this->connecteurEntiteSQL->delete($id_ce);
        $this->connecteurActionService->delete($id_ce);
        $this->jobManager->deleteConnecteur($id_ce);
    }

    public function disassociate(int $connectorId): void
    {
        foreach ($this->fluxEntiteSQL->getUsedByConnecteur($connectorId) as $association) {
            $this->fluxEntiteSQL->removeConnecteur($association['id_fe']);
        }
    }
}
