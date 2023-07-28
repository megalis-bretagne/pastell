<?php

declare(strict_types=1);

namespace Pastell\Configuration\Validators;

use Pastell\Configuration\ModuleElement;
use Pastell\Service\Pack\PackService;

class RestrictionPackValidator implements ValidatorInterface
{
    private array $errors;

    public function __construct(
        private readonly PackService $packService,
    ) {
    }

    public function validate(array $typeDefinition): bool
    {
        $this->errors = [];
        $allRestrictionPack = $typeDefinition[ModuleElement::RESTRICTION_PACK->value];
        foreach ($allRestrictionPack as $restrictionPack) {
            if (!array_key_exists($restrictionPack, $this->packService->getListPack())) {
                $this->errors[] = "restriction_pack:<b>$restrictionPack</b> "
                    . "n'est pas défini dans la liste des packs";
            }
        }
        return count($this->errors) === 0;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
