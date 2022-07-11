<?php

declare(strict_types=1);

namespace Pastell\Seda\Message\Part;

final class File implements \JsonSerializable
{
    public string $filename;
    public string $uri;
    public string $messageDigest;
    public string $size;
    public ?string $mimeType = null;
    public string $title;

    public function __construct(
        private string $id,
    ) {
    }

    public function jsonSerialize(): array
    {
        return \array_filter([
            'Id' => $this->id,
            'Filename' => $this->filename,
            'Uri' => $this->uri,
            'MessageDigest' => $this->messageDigest,
            'Size' => $this->size,
            'MimeType' => $this->mimeType,
            'Title' => $this->title,
        ]);
    }
}
