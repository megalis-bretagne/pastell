<?php

declare(strict_types=1);

namespace Pastell\Utilities\Identifier;

class UuidGenerator implements IdentifierGeneratorInterface
{
    public function generate(): string
    {
        return uuid_create(UUID_TYPE_RANDOM);
    }
}
