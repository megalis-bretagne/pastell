<?php

declare(strict_types=1);

namespace Pastell\Utilities\Identifier;

interface IdentifierInterface
{
    public function generateId(): string;
}
