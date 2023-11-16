<?php

declare(strict_types=1);

namespace Pastell\Seda\Message\Part;

use function Pastell\areNullOrEmptyStrings;

final class File implements \JsonSerializable
{
    public string $filename;
    public string $uri;
    public string $messageDigest;
    public string $algorithmIdentifier;
    public string $size;
    public ?string $mimeType = null;
    public string $title;
    private ?ContentDescription $contentDescription = null;

    public function __construct(
        private readonly string $id,
    ) {
    }

    public function jsonSerialize(): array
    {
        return \array_filter([
            'Id' => $this->id,
            'Filename' => $this->filename,
            'Uri' => $this->uri,
            'MessageDigest' => $this->messageDigest,
            'AlgorithmIdentifier' => $this->algorithmIdentifier,
            'Size' => $this->size,
            'MimeType' => $this->mimeType,
            'Title' => $this->title,
            'ContentDescription' => $this->contentDescription,
        ]);
    }

    /**
     * @param Keyword[] $keywords
     */
    public function setContentDescription(
        ?string $description,
        ?string $descriptionLevel,
        ?string $language,
        ?string $custodialHistory,
        ?array $keywords
    ): self {
        if (
            $keywords !== null || !areNullOrEmptyStrings(
                $description,
                $descriptionLevel,
                $language,
                $custodialHistory
            )
        ) {
            $contentDescription = new ContentDescription();
            $contentDescription->description = $description;
            $contentDescription->descriptionLevel = $descriptionLevel;
            $contentDescription->language = $language;
            $contentDescription->custodialHistory = $custodialHistory;
            $contentDescription->keywords = $keywords;
            $this->contentDescription = $contentDescription;
        }
        return $this;
    }
}
