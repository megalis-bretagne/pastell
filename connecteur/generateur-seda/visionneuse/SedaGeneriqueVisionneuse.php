<?php

declare(strict_types=1);

use Pastell\Connector\AbstractSedaGeneratorConnector;
use Pastell\Viewer\ConnectorViewer;

class SedaGeneriqueVisionneuse extends ConnectorViewer
{
    /**
     * @throws \JsonException
     */
    public function display(string $filename, string $filepath): void
    {
        if (!$filepath || !\file_exists($filepath)) {
            echo "<br/>Aucune donnée n'a été renseignée.<br/>";
            return;
        }

        /** @var AbstractSedaGeneratorConnector $connector */
        $connector = $this->getConnector();
        $pastell2Seda = $connector->getPastellToSeda();
        $content = \json_decode(\file_get_contents($filepath), true, 512, \JSON_THROW_ON_ERROR);

        $str = '';
        foreach ($content as $key => $value) {
            $header = \get_hecho($pastell2Seda[$key]['libelle'] ?? $key);
            $cell = \nl2br(\get_hecho($value));
            $str .= <<<EOT
<tr>
    <th class="w500">$header</th>
    <td>$cell</td>
</tr>
EOT;
        }

        echo <<<EOT
<table class="table table-striped" aria-label="Données du bordereau">
$str
</table>
EOT;
    }
}
