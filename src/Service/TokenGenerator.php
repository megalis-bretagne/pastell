<?php

namespace Pastell\Service;

use Exception;

class TokenGenerator
{
    private const DEFAULT_ENTROPY_LENGTH = 32;

    /**
     * Thanks https://stackoverflow.com/a/51947616
     * @param int $entropyLength
     * @return string
     * @throws Exception
     */
    public function generate(int $entropyLength = self::DEFAULT_ENTROPY_LENGTH): string
    {
        return rtrim(strtr(base64_encode(random_bytes($entropyLength)), '+/', '-_'), '=');
    }
}
