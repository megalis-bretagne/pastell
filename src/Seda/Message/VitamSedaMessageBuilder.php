<?php

declare(strict_types=1);

namespace Pastell\Seda\Message;

use Pastell\Seda\SedaVersion;

final class VitamSedaMessageBuilder extends SedaMessageBuilder
{
    public const BASE_CONTENT_DIRECTORY = 'content';

    public function setVersion(string $version): static
    {
        return parent::setVersion(SedaVersion::VERSION_2_1_VITAM->value);
    }

    protected function normalizeUri(string $filepath, string $digest): string
    {
        $output = self::BASE_CONTENT_DIRECTORY . '/' . $digest;
        $extension = \pathinfo($filepath, \PATHINFO_EXTENSION);
        if ($extension !== '') {
            $output .= '.' . $extension;
        }
        return $output;
    }
}
