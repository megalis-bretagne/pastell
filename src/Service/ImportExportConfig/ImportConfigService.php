<?php

namespace Pastell\Service\ImportExportConfig;

use ConnecteurFactory;
use DonneesFormulaireException;
use Exception;
use FluxEntiteHeritageSQL;
use Pastell\Service\Connecteur\ConnecteurAssociationService;
use Pastell\Service\Connecteur\ConnecteurCreationService;
use Pastell\Service\Entite\EntityCreationService;
use UnrecoverableException;

final class ImportConfigService
{
    //Ne permets pas d'importer les centres de gestion des collectivités !

    /** @var string[]  */
    private array $lastErrors = [];

    public function __construct(
        private readonly EntityCreationService $entityCreationService,
        private readonly ConnecteurFactory $connecteurFactory,
        private readonly FluxEntiteHeritageSQL $fluxEntiteHeritageSQL,
        private readonly ConnecteurCreationService $connecteurCreationService,
        private readonly ConnecteurAssociationService $connecteurAssociationService,
    ) {
    }

    public function getLastErrors(): array
    {
        return $this->lastErrors;
    }

    /**
     * @param array $exportedData
     * @param int $id_e_root
     * @throws DonneesFormulaireException
     */
    public function import(array $exportedData, int $id_e_root): void
    {
        $this->lastErrors = [];
        $id_e_mapping = $this->importEntity($exportedData, $id_e_root);
        $id_e_mapping = $this->importChildEntity($exportedData, $id_e_mapping, $id_e_root);
        $connectorMapping = $this->importConnector($exportedData, $id_e_mapping, $id_e_root);
        $this->importAssociation($exportedData, $id_e_mapping, $connectorMapping, $id_e_root);
        $this->importAssociationInheritance($exportedData, $id_e_mapping, $id_e_root);
    }

    /**
     * @throws UnrecoverableException
     */
    private function importEntity(array $exportedData, int $id_e_root): array
    {
        $id_e_mapping = [];
        if (empty($exportedData[ExportConfigService::ENTITY_INFO])) {
            return $id_e_mapping;
        }
        $entityInfo = $exportedData[ExportConfigService::ENTITY_INFO];
        $id_e_entity = $this->entityCreationService->create(
            $entityInfo['denomination'],
            $entityInfo['siren'],
            $entityInfo['type'],
            $id_e_root
        );
        $id_e_mapping[$entityInfo['id_e']] = $id_e_entity;
        return $id_e_mapping;
    }

    /**
     * @throws UnrecoverableException
     */
    public function importChildEntity(array $exportedData, array $id_e_mapping, int $id_e_root): array
    {
        if (empty($exportedData[ExportConfigService::ENTITY_CHILD])) {
            return $id_e_mapping;
        }
        foreach ($exportedData[ExportConfigService::ENTITY_CHILD] as $entity_child) {
            if (empty($id_e_mapping[$entity_child['entite_mere']])) {
                $this->lastErrors[] = "L'entité mère de {$entity_child['denomination']} est inconnue, l'entité sera attachée à l'entité $id_e_root.";
                $entity_child['entite_mere'] = $id_e_root;
            } else {
                $entity_child['entite_mere'] = $id_e_mapping[$entity_child['entite_mere']];
            }
            $id_e_entity = $this->entityCreationService->create(
                $entity_child['denomination'],
                $entity_child['siren'],
                $entity_child['type'],
                $entity_child['entite_mere'],
            );
            $id_e_mapping[$entity_child['id_e']] = $id_e_entity;
        }
        return $id_e_mapping;
    }

    /**
     * @param array $exportedData
     * @param array $id_e_mapping
     * @return array
     * @throws DonneesFormulaireException
     * @throws Exception
     */
    private function importConnector(array $exportedData, array $id_e_mapping, int $id_e_root): array
    {
        $connectorMapping = [];
        if (empty($exportedData[ExportConfigService::CONNECTOR_INFO])) {
            return $connectorMapping;
        }
        foreach ($exportedData[ExportConfigService::CONNECTOR_INFO] as $connecteurInfo) {
            if ($connecteurInfo['id_e'] !== 0 && empty($id_e_mapping[$connecteurInfo['id_e']])) {
                if ($id_e_root === 0) {
                    $this->lastErrors[] = "Le connecteur {$connecteurInfo['libelle']} est attaché à une entité inconnue : il n'a pas été importé.";
                    continue;
                }
                $this->lastErrors[] = "Le connecteur {$connecteurInfo['libelle']} est attaché à une entité inconnue : il sera attaché à l'entité $id_e_root.";
                $id_e_mapping[$connecteurInfo['id_e']] = $id_e_root;
            }
            $connecteurInfo['id_e'] = $id_e_mapping[$connecteurInfo['id_e']];
            $id_ce = $this->connecteurCreationService->createConnecteur(
                $connecteurInfo['id_connecteur'],
                $connecteurInfo['type'],
                $connecteurInfo['id_e'],
                0,
                $connecteurInfo['libelle']
            );
            $connectorMapping[$connecteurInfo['id_ce']] = $id_ce;
            $connecteurConfig = $this->connecteurFactory->getConnecteurConfig($id_ce);
            $connecteurConfig->jsonImport($connecteurInfo['data']);
        }
        return $connectorMapping;
    }

    private function importAssociation(array $exportedData, array $id_e_mapping, array $connectorMapping, int $id_e_root): void
    {
        if (empty($exportedData[ExportConfigService::ASSOCIATION_INFO])) {
            return;
        }
        foreach ($exportedData[ExportConfigService::ASSOCIATION_INFO] as $id_e => $fluxInfo) {
            if ($id_e !== 0 && empty($id_e_mapping[$id_e])) {
                if ($id_e_root === 0) {
                    $this->lastErrors[] = "L'entité du fichier d'import id_e=$id_e n'est pas présente : ces associations n'ont pas été importées.";
                    continue;
                }
                $this->lastErrors[] = "L'entité du fichier d'import id_e=$id_e n'est pas présente : associations importées sur l'entité $id_e_root.";
                $id_e = $id_e_root;
            }
            foreach ($fluxInfo as $flux_name => $connecteurInfo) {
                foreach ($connecteurInfo as $typeFlux => $listConnecteurInfo) {
                    foreach ($listConnecteurInfo as $theConnectorInfo) {
                        $this->associateConnector($theConnectorInfo, $id_e_mapping[$id_e], $flux_name, $typeFlux, $connectorMapping);
                    }
                }
            }
        }
    }

    private function associateConnector(array $theConnectorInfo, int $id_e, string $flux_name, string $typeFlux, array $connectorMapping): void
    {
        if (empty($connectorMapping[$theConnectorInfo['id_ce']])) {
            $this->lastErrors[] = "La définition du connecteur id_ce={$theConnectorInfo['id_ce']} n'est pas présente : l'association n'a pas été importée.";
            return;
        }
        $this->connecteurAssociationService->addConnecteurAssociation(
            $id_e,
            $connectorMapping[$theConnectorInfo['id_ce']],
            $typeFlux,
            0,
            $flux_name,
            $theConnectorInfo['num_same_type']
        );
    }

    private function importAssociationInheritance(array $exportedData, array $id_e_mapping, int $id_e_root): void
    {
        if (empty($exportedData[ExportConfigService::ASSOCIATION_HERITAGE_INFO])) {
            return;
        }
        foreach ($exportedData[ExportConfigService::ASSOCIATION_HERITAGE_INFO] as $id_e => $heritage_list) {
            if (empty($id_e_mapping[$id_e])) {
                if ($id_e_root === 0) {
                    $this->lastErrors[] = "L'entité du fichier d'import id_e=$id_e n'est pas présente : les héritages d'associations n'ont pas été importées.";
                    continue;
                }
                $this->lastErrors[] = "L'entité du fichier d'import id_e=$id_e n'est pas présente : les héritages d'associations sont importées sur $id_e_root.";
                $id_e_mapping[$id_e] = $id_e_root;
            }
            foreach ($heritage_list as $flux) {
                $this->fluxEntiteHeritageSQL->setInheritance($id_e_mapping[$id_e], $flux);
            }
        }
    }
}
