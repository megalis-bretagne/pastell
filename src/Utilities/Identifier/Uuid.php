<?php

declare(strict_types=1);

namespace Pastell\Utilities\Identifier;

class Uuid implements IdentifierInterface
{

    public function generateId(): string
    {
        return uuid_create(UUID_TYPE_RANDOM);
    }
}
