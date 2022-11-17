<?php

declare(strict_types=1);

namespace Pastell\Seda\Message\Part;

use function Pastell\areNullOrEmptyStrings;

final class ArchiveUnit implements \JsonSerializable
{
    public ?string $title = null;
    private ?ContentDescription $contentDescription = null;
    private ?AccessRestrictionRule $accessRestrictionRule = null;
    private ?AppraisalRule $appraisalRule = null;
    /** @var File[] $files */
    private array $files = [];
    /** @var self[] $archiveUnits */
    private array $archiveUnits = [];

    public function __construct(
        private readonly string $id,
    ) {
    }

    /**
     * @return ArchiveUnit[]
     */
    public function getArchiveUnits(): array
    {
        return $this->archiveUnits;
    }

    /**
     * @return File[]
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * @param Keyword[] $keywords
     */
    public function setContentDescription(
        ?string $descriptionLevel,
        ?string $language,
        ?string $custodialHistory,
        ?array $keywords
    ): self {
        if ($keywords !== null || !areNullOrEmptyStrings($descriptionLevel, $language, $custodialHistory)) {
            $contentDescription = new ContentDescription();
            $contentDescription->descriptionLevel = $descriptionLevel;
            $contentDescription->language = $language;
            $contentDescription->custodialHistory = $custodialHistory;
            $contentDescription->keywords = $keywords;
            $this->contentDescription = $contentDescription;
        }
        return $this;
    }

    public function setAccessRestrictionRule(?string $accessRule, ?string $startDate): self
    {
        if (!areNullOrEmptyStrings($accessRule, $startDate)) {
            $accessRestrictionRule = new  AccessRestrictionRule();
            $accessRestrictionRule->accessRule = $accessRule;
            $accessRestrictionRule->startDate = $startDate;
            $this->accessRestrictionRule = $accessRestrictionRule;
        }

        return $this;
    }

    public function setAppraisalRule(?string $rule, ?string $finalAction, ?string $startDate): self
    {
        if (!areNullOrEmptyStrings($rule, $finalAction, $startDate)) {
            $this->appraisalRule = new AppraisalRule($rule, $finalAction, $startDate);
        }

        return $this;
    }

    public function addArchiveUnit(ArchiveUnit $archiveUnit): self
    {
        $this->archiveUnits[] = $archiveUnit;
        return $this;
    }

    public function setFiles(array $files): self
    {
        $this->files = $files;
        return $this;
    }

    public function jsonSerialize(): array
    {
        return \array_filter([
            'Id' => $this->id,
            'Title' => $this->title,
            'ContentDescription' => $this->contentDescription,
            'AccessRestrictionRule' => $this->accessRestrictionRule,
            'AppraisalRule' => $this->appraisalRule,
            'ArchiveUnits' => $this->archiveUnits,
            'Files' => $this->files,
        ]);
    }
}
