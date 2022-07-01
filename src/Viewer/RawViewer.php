<?php

declare(strict_types=1);

namespace Pastell\Viewer;

final class RawViewer implements Viewer
{
    public function display(string $filename, string $filepath): void
    {
        if (!\file_exists($filepath)) {
            echo "Aucun fichier présent";
            return;
        }
        \hecho(\file_get_contents($filepath));
    }
}
