<?php

namespace Pastell\Service\ChorusPro;

use ActionExecutorFactory;
use Monolog\Logger;
use CPPException;
use Exception;
use DonneesFormulaireFactory;

class ChorusProImportSynchroService
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var ActionExecutorFactory
     */
    private $actionExecutorFactory;

    /**
     * @var DonneesFormulaireFactory
     */
    private $donneesFormulaireFactory;

    /** @var  int */
    private $id_e;
    /** @var  int */
    private $id_u;

    public function setChorusProConfigService(int $id_e, int $id_u)
    {
        $this->id_e = $id_e;
        $this->id_u = $id_u;
    }

    public function __construct(
        Logger $logger,
        ActionExecutorFactory $actionExecutorFactory,
        DonneesFormulaireFactory $donneesFormulaireFactory
    ) {
        $this->logger = $logger;
        $this->actionExecutorFactory = $actionExecutorFactory;
        $this->donneesFormulaireFactory = $donneesFormulaireFactory;
    }

    /**
     * @param array $facture_chorus
     * @param array $facture_pastell
     * @return array
     */
    public function analyseOneFactureSynchro(array $facture_chorus, array $facture_pastell): array
    {
        if ($facture_pastell['statut_cpp'] !==  $facture_chorus['statut']) {
            $result = $this->synchroniserFacture($facture_pastell['id_d']);
        } else {
            // Rien à faire. On trace seulement l'information.
            $result['id_d'] = $facture_pastell['id_d'];
            $result['message'] = "Le statut de la facture (" . $facture_chorus['statut'] . ") est identique sur le bus et sur la plateforme Chorus.";
            $result['resultat'] = true;
        }
        $donneesFormulaire = $this->donneesFormulaireFactory->get($facture_pastell['id_d']);
        $donneesFormulaire->setData('date_statut_courant', $facture_chorus['date_statut_courant']);
        $result['id_facture_cpp'] = $facture_chorus['id_facture_cpp'];
        $result['type'] = ChorusProImportUtilService::TYPE_SYNCHRONISATION_SYNCHRO;

        return $result;
    }

    /**
     * @param string $id_d
     * @return array
     */
    public function synchroniserFacture(string $id_d): array
    {
        $this->logger->info("Resynchro : $id_d");

        $result['id_d'] = $id_d;
        try {
            $result['message'] = $this->synchroniseStatus($id_d);
            $result['resultat'] = true;
        } catch (Exception $e) {
            $result['resultat'] = false;
            $result['message'] = $e->getMessage();
        }
        return $result;
    }

    /**
     * @param string $id_d
     * @return mixed
     * @throws CPPException
     */
    private function synchroniseStatus(string $id_d): string
    {
        $actionExecutorFactory = $this->actionExecutorFactory;
        if (! $actionExecutorFactory->executeOnDocumentThrow($id_d, $this->id_e, $this->id_u, 'synchroniser-statut', array(), false, array(), 0)) {
            throw new CPPException($actionExecutorFactory->getLastMessage());
        }
        return $actionExecutorFactory->getLastMessage();
    }
}
