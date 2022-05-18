<?php

declare(strict_types=1);

namespace Pastell\Seda\Message\Part;

final class Keyword implements \JsonSerializable
{
    public ?string $keywordContent;
    public ?string $keywordReference;
    public ?string $keywordType;

    public function jsonSerialize(): array
    {
        return \array_filter([
            'KeywordContent' => $this->keywordContent,
            'KeywordReference' => $this->keywordReference,
            'KeywordType' => $this->keywordType,
        ]);
    }
}
