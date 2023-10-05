<?php

namespace Pastell\Service\Connecteur;

use ConnecteurEntiteSQL;
use ConnecteurFactory;
use DonneesFormulaireFactory;
use Exception;

class ConnecteurCreationService
{
    private $connecteurFactory;
    private $connecteurEntiteSQL;
    private $connecteurActionService;
    private $connecteurAssociationService;
    private $donneesFormulaireFactory;

    public function __construct(
        ConnecteurFactory $connecteurFactory,
        ConnecteurEntiteSQL $connecteurEntiteSQL,
        ConnecteurActionService $connecteurActionService,
        ConnecteurAssociationService $connecteurAssociationService,
        DonneesFormulaireFactory $donneesFormulaireFactory
    ) {
        $this->connecteurFactory = $connecteurFactory;
        $this->connecteurEntiteSQL = $connecteurEntiteSQL;
        $this->connecteurActionService = $connecteurActionService;
        $this->connecteurAssociationService = $connecteurAssociationService;
        $this->donneesFormulaireFactory = $donneesFormulaireFactory;
    }

    /**
     * @throws Exception
     */
    public function createConnecteur(
        string $connecteur_id,
        string $type,
        int $id_e = 0,
        int $id_u = 0,
        string $libelle = '',
        array $data = [],
        string $message = ''
    ): int {

        $libelle = ($libelle == '') ? $connecteur_id : $libelle;

        $id_ce =  $this->connecteurEntiteSQL->addConnecteur(
            $id_e,
            $connecteur_id,
            $type,
            $libelle
        );

        $donneesFormulaire = $this->donneesFormulaireFactory->getConnecteurEntiteFormulaire($id_ce);
        $donneesFormulaire->setTabData($data);

        $this->connecteurActionService->add(
            $id_e,
            $id_u,
            $id_ce,
            '',
            ConnecteurActionService::ACTION_AJOUTE,
            $message
        );

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
     * @param string $libelle
     * @param array $data
     * @return int
     * @throws Exception
     */
    public function createAndAssociateGlobalConnecteur(
        string $connecteur_id,
        string $type,
        string $libelle = '',
        array $data = []
    ): int {
        $id_ce = $this->createConnecteur(
            $connecteur_id,
            $type,
            0,
            0,
            $libelle,
            $data,
            "Le connecteur $connecteur_id « $libelle » a été créé par « Pastell »"
        );

        $this->connecteurAssociationService->addConnecteurAssociation(
            0,
            $id_ce,
            $type
        );

        return $id_ce;
    }
}
