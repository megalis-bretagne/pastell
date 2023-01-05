<?php

declare(strict_types=1);

namespace Pastell\Storage;

class StorageInterfaceNone implements StorageInterface
{
    public function write(string $id, string $content): string
    {
        return '';
    }

    public function read(string $id): string
    {
        return '';
    }
}