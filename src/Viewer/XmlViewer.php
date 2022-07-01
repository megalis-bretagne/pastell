<?php

declare(strict_types=1);

namespace Pastell\Viewer;

use XMLFormattage;

final class XmlViewer implements Viewer
{
    public function __construct(private XMLFormattage $xmlFormattage)
    {
    }

    public function display(string $filename, string $filepath): void
    {
        echo "<pre>";
        \hecho($this->xmlFormattage->getString($filepath));
        echo "</pre>";
    }
}
