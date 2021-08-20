<?php

namespace Pastell\Service\TypeDossier;

use TypeDossierActionSQL;
use Pastell\Service\TypeDossier\TypeDossierExportService;
use Pastell\Service\TypeDossier\TypeDossierManager;

class TypeDossierActionService
{
    public const ACTION_MODIFFIE = 'Modifié';
    public const ACTION_AJOUTE = 'Ajouté';

    /**
     * @var TypeDossierActionSQL
     */
    private $typeDossierActionSQL;

    /**
     * @var TypeDossierExportService
     */
    private $typeDossierExportService;

    /**
     * @var TypeDossierManager
     */
    private $typeDossierManager;

    public function __construct(
        TypeDossierActionSQL $typeDossierActionSQL,
        TypeDossierExportService $typeDossierExportService,
        TypeDossierManager $typeDossierManager
    ) {
        $this->typeDossierActionSQL = $typeDossierActionSQL;
        $this->typeDossierExportService = $typeDossierExportService;
        $this->typeDossierManager = $typeDossierManager;
    }

    public function add(int $id_u, int $id_t, string $action): int
    {
        return $this->typeDossierActionSQL->add(
            $id_u,
            $id_t,
            $action,
            $this->typeDossierManager->getHash($id_t),
            $this->typeDossierExportService->export($id_t)
        );
    }

    public function getById(int $id_t): array
    {
        return $this->typeDossierActionSQL->getById($id_t);
    }
}
