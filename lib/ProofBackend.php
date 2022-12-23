<?php

declare(strict_types=1);

interface ProofBackend
{
    public function write($id, $content): void;

    public function read($id): string;
}
