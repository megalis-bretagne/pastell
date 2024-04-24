<?php

declare(strict_types=1);

use Pastell\Viewer\Viewer;

class TransformationGeneriqueVisionneuse implements Viewer
{
    /**
     * @throws UnrecoverableException
     * @throws \JsonException
     */
    public function display(string $filename, string $filepath): void
    {
        if (!$filepath || !\is_readable($filepath))  {
            echo "Aucune donnée n'a été renseignée";
            return;
        }

        $content = \json_decode(\file_get_contents($filepath), true, 512, \JSON_THROW_ON_ERROR);

        $rows = '';
        foreach ($content as $elementId => $expression) {
            $header = \get_hecho($elementId);
            $cell = \nl2br(\get_hecho($expression));
            $rows .= <<<EOT
<tr>
    <th class="w500">$header</th>
    <td>$cell</td>
</tr>
EOT;
        }

        echo <<<EOT
<table class="table table-striped" aria-label="Définition de l'extraction">
    $rows
</table>
EOT;
    }
}
