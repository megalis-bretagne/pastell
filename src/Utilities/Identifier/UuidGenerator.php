<?php

declare(strict_types=1);

namespace Pastell\Utilities\Identifier;

use Symfony\Component\Uid\Uuid;

class UuidGenerator implements IdentifierGeneratorInterface
{
    public function generate(): string
    {
        return Uuid::v4()->jsonSerialize();
    }
}
