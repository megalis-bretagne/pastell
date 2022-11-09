<?php

/**
 * @deprecated 4.0.0
 */
final class SignedFile
{
    /** @var string */
    public $extension;
    /** @var string */
    public $signature;

    public function __construct(string $signature, string $extension)
    {
        $this->signature = $signature;
        $this->extension = $extension;
    }
}
