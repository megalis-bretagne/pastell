<?php

declare(strict_types=1);

namespace Pastell\Utilities\Identifier;

interface IdentifierGeneratorInterface
{
    public function generate(): string;
}
