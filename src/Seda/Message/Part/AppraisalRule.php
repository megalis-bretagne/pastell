<?php

declare(strict_types=1);

namespace Pastell\Seda\Message\Part;

final class AppraisalRule implements \JsonSerializable
{
    public ?string $finalAction;
    public ?string $rule;
    public ?string $startDate;

    public function __construct(?string $rule, ?string $finalAction, ?string $startDate)
    {
        $this->finalAction = $finalAction;
        $this->rule = $rule;
        $this->startDate = $startDate;
    }

    public function jsonSerialize(): array
    {
        return \array_filter([
            'FinalAction' => $this->finalAction,
            'Rule' => $this->rule,
            'StartDate' => $this->startDate,
        ]);
    }
}
