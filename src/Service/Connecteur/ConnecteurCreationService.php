<?php

namespace Pastell\Service\Connecteur;

use ConnecteurEntiteSQL;
use Pastell\Service\Connecteur\ConnecteurActionService;
use ConnecteurFactory;
use DonneesFormulaireFactory;
use Exception;
use FluxEntiteSQL;

class ConnecteurCreationService
{
    private $connecteurFactory;
    private $connecteurEntiteSQL;
    private $connecteurActionService;
    private $donneesFormulaireFactory;
    private $fluxEntiteSQL;

    public function __construct(
        ConnecteurFactory $connecteurFactory,
        ConnecteurEntiteSQL $connecteurEntiteSQL,
        ConnecteurActionService $connecteurActionService,
        DonneesFormulaireFactory $donneesFormulaireFactory,
        FluxEntiteSQL $fluxEntiteSQL
    ) {
        $this->connecteurFactory = $connecteurFactory;
        $this->connecteurEntiteSQL = $connecteurEntiteSQL;
        $this->connecteurActionService = $connecteurActionService;
        $this->donneesFormulaireFactory = $donneesFormulaireFactory;
        $this->fluxEntiteSQL = $fluxEntiteSQL;
    }

    /**
     * @throws Exception
     */
    public function createConnecteur(int $id_e, string $connecteur_id, string $type, string $libelle, array $data = []): int
    {
        $id_ce =  $this->connecteurEntiteSQL->addConnecteur(
            $id_e,
            $connecteur_id,
            $type,
            $libelle
        );

        $donneesFormulaire = $this->donneesFormulaireFactory->getConnecteurEntiteFormulaire($id_ce);
        $donneesFormulaire->setTabData($data);

        return $id_ce;
    }
    /**
     * @param $type
     * @return bool
     */
    public function hasConnecteurGlobal($type): bool
    {
        $connecteurGlobal = $this->connecteurFactory->getGlobalConnecteur($type);
        return (bool) $connecteurGlobal;
    }

    /**
     * @param string $connecteur_id
     * @param string $type
     * @param array $data
     * @return int
     * @throws Exception
     */
    public function createAndAssociateGlobalConnecteur(string $connecteur_id, string $type, array $data = []): int
    {
        $id_ce = $this->createConnecteur(0, $connecteur_id, $type, $connecteur_id, $data);
        $this->connecteurActionService->add(
            0,
            0,
            $id_ce,
            '',
            ConnecteurActionService::ACTION_AJOUTE,
            "Le connecteur $type a été créé par « Pastell »"
        );

        $this->fluxEntiteSQL->addConnecteur(
            0,
            FluxEntiteSQL::FLUX_GLOBAL_NAME,
            $type,
            $id_ce
        );

        return $id_ce;
    }
}
