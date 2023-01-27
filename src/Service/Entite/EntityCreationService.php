<?php

declare(strict_types=1);

namespace Pastell\Service\Entite;

use EntiteSQL;
use Journal;
use Pastell\Service\Validator\EntityValidator;
use UnrecoverableException;

final class EntityCreationService
{
    public function __construct(
        private readonly EntiteSQL $entiteSQL,
        private readonly Journal $journal,
        private readonly EntityValidator $validator,
    ) {
    }

    /**
     * @throws UnrecoverableException
     */
    public function create(
        string $name,
        string $siren,
        string $type = EntiteSQL::TYPE_COLLECTIVITE,
        int $parent = 0,
        int $cdg = 0,
    ): int {
        $this->validator->validate($name, $siren, $type, $parent, $cdg);

        $entityId = $this->entiteSQL->create($name, $siren, $type, $parent, $cdg);

        $this->journal->add(
            Journal::MODIFICATION_ENTITE,
            $entityId,
            0,
            Journal::ACTION_CREATED,
            "Création de l'entité $name - $siren"
        );
        $this->entiteSQL->updateAncestor($entityId, $parent);

        return $entityId;
    }
}
