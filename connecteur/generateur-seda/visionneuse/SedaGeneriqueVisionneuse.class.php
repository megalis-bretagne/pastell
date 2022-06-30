<?php

declare(strict_types=1);

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

        /** @var SedaGenerique $connector */
        $connector = $this->getConnector();
        $pastell2Seda = $connector::getPastellToSeda();
        $content = \json_decode(\file_get_contents($filepath), true, 512, \JSON_THROW_ON_ERROR);
        ?>
        <table class="table table-striped" aria-label="Données du bordereau">
            <?php
            foreach ($content as $key => $value) : ?>
                <tr>
                    <th class="w500"><?php
                        \hecho($pastell2Seda[$key]['libelle'] ?? $key); ?></th>
                    <td><?php
                        echo \nl2br(\get_hecho($value)); ?></td>
                </tr>
                <?php
            endforeach; ?>
        </table>
        <?php
    }
}
