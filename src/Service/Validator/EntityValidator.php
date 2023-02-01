<?php

declare(strict_types=1);

namespace Pastell\Service\Validator;

use EntiteSQL;
use Siren;
use UnrecoverableException;

final class EntityValidator
{
    public function __construct(
        private readonly EntiteSQL $entiteSQL,
        private readonly Siren $siren,
    ) {
    }

    /**
     * TODO: Replace parameters with a real entity object
     * @throws UnrecoverableException
     */
    public function validate(string $name, string $siren, string $type, int $parent, int $cdg): bool
    {
        if (!$name) {
            throw new UnrecoverableException('Le nom (denomination) est obligatoire');
        }

        if ($siren !== '' && !$this->siren->isValid($siren)) {
            throw new UnrecoverableException("Le siren « $siren » ne semble pas valide");
        }

        if (!\array_key_exists($type, EntiteSQL::getAllType())) {
            throw new UnrecoverableException(
                "Le type d'entité doit être renseigné. Les valeurs possibles sont collectivite ou centre_de_gestion."
            );
        }

        if (!$this->entiteSQL->isActive($parent)) {
            throw new UnrecoverableException("L'entité $parent ne peut pas être utilisée comme entité mère");
        }

        if (!$this->entiteSQL->isCDG($cdg)) {
            throw new UnrecoverableException("L'entité $cdg ne peut pas être utilisée comme centre de gestion");
        }

        return true;
    }
}
