<?php

declare(strict_types=1);

namespace Pastell\Seda\Message\Part;

final class ContentDescription implements \JsonSerializable
{
    public ?string $descriptionLevel;
    public ?string $language;
    public ?string $custodialHistory;
    /** @var Keyword[] $keywords */
    public ?array $keywords;

    public function jsonSerialize(): array
    {
        return \array_filter([
            'DescriptionLevel' => $this->descriptionLevel,
            'Language' => $this->language,
            'CustodialHistory' => $this->custodialHistory,
            'Keywords' => $this->keywords,
        ]);
    }
}
