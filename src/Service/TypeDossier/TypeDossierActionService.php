<?php

namespace Pastell\Service\TypeDossier;

use TypeDossierActionSQL;
use Pastell\Service\TypeDossier\TypeDossierManager;

class TypeDossierActionService
{
    public const ACTION_MODIFFIE = 'Modifié';
    public const ACTION_AJOUTE = 'Ajouté';

    private $id_u;

    /**
     * @var TypeDossierActionSQL
     */
    private $typeDossierActionSQL;

    /**
     * @var TypeDossierManager
     */
    private $typeDossierManager;

    public function __construct(
        TypeDossierActionSQL $typeDossierActionSQL,
        TypeDossierManager $typeDossierManager
    ) {
        $this->typeDossierActionSQL = $typeDossierActionSQL;
        $this->typeDossierManager = $typeDossierManager;
    }

    public function add(int $id_u, int $id_t, string $action, string $message): int
    {
        return $this->typeDossierActionSQL->add(
            $id_u,
            $id_t,
            $action,
            $this->typeDossierManager->getHash($id_t),
            $message
        );
    }

    public function getById(int $id_t, int $offset = 0, int $limit = TypeDossierActionSQL::DEFAULT_LIMIT): array
    {
        return $this->typeDossierActionSQL->getById($id_t, $offset, $limit);
    }

    public function countById(int $id_t): int
    {
        return $this->typeDossierActionSQL->countById($id_t);
    }
}
