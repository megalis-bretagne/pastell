<?php

declare(strict_types=1);

namespace Pastell\Seda\Message\Part;

final class AccessRestrictionRule implements \JsonSerializable
{
    public ?string $accessRule;
    public ?string $startDate;

    public function jsonSerialize(): array
    {
        return \array_filter([
            'AccessRule' => $this->accessRule,
            'StartDate' => $this->startDate,
        ]);
    }
}
