<?php

namespace Pastell\Service\Connecteur;

use ConnecteurEntiteSQL;
use ConnecteurFactory;
use DonneesFormulaireFactory;
use Exception;
use FluxEntiteSQL;

class ConnecteurCreationService
{
    private $connecteurFactory;
    private $connecteurEntiteSQL;
    private $donneesFormulaireFactory;
    private $fluxEntiteSQL;

    public function __construct(
        ConnecteurFactory $connecteurFactory,
        ConnecteurEntiteSQL $connecteurEntiteSQL,
        DonneesFormulaireFactory $donneesFormulaireFactory,
        FluxEntiteSQL $fluxEntiteSQL
    ) {
        $this->connecteurFactory = $connecteurFactory;
        $this->connecteurEntiteSQL = $connecteurEntiteSQL;
        $this->donneesFormulaireFactory = $donneesFormulaireFactory;
        $this->fluxEntiteSQL = $fluxEntiteSQL;
    }

    /**
     * @param string $type
     * @param string $connecteur_id
     * @param array $data
     * @return int|false
     * @throws Exception
     */
    public function createAndAssociateGlobalConnecteur(string $type, string $connecteur_id, array $data): int
    {
        $connecteurGlobal = $this->connecteurFactory->getGlobalConnecteur($type);

        if ($connecteurGlobal) {
            return false;
        }

        $id_ce =  $this->connecteurEntiteSQL->addConnecteur(
            0,
            $connecteur_id,
            $type,
            "$connecteur_id"
        );

        $donneesFormulaire = $this->donneesFormulaireFactory->getConnecteurEntiteFormulaire($id_ce);
        $donneesFormulaire->setTabData($data);

        $this->fluxEntiteSQL->addConnecteur(0, 'global', 'visionneuse_pes', $id_ce);

        return $id_ce;
    }
}
