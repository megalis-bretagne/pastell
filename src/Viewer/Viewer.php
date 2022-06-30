<?php

declare(strict_types=1);

namespace Pastell\Viewer;

interface Viewer
{
    public function display(string $filename, string $filepath): void;
}
