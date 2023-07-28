<?php

declare(strict_types=1);

namespace Pastell\Configuration\Validators;

interface ValidatorInterface
{
    public function validate(array $typeDefinition): bool;
    public function getErrors(): array;
}
