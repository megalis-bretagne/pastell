<?php

declare(strict_types=1);

namespace Pastell\Viewer;

use TdtConnecteur;

final class ReponsePrefectureLinkViewer implements Viewer
{
    /**
     * @throws \JsonException
     */
    public function display(string $filename, string $filepath): void
    {
        if (!\file_exists($filepath)) {
            echo "Le fichier n'existe pas";
            return;
        }
        $filecontent = \file_get_contents($filepath);

        $json = \json_decode($filecontent, true, 512, \JSON_THROW_ON_ERROR);
        if (!$json) {
            echo "Le fichier est vide";
            return;
        }
        ?>
        <table style="border-style: solid; border-width: thin;" aria-label="Réponses de la préfecture">
            <tr>
                <th>Type de réponse</th>
                <th>URL</th>
            </tr>
            <?php
            foreach ($json as $type_reponse => $url) : ?>
                <tr>
                    <td>
                        <?php
                        \hecho(TdtConnecteur::getTransactionNameFromNumber($type_reponse) ?? 'ERREUR') ?>
                    </td>
                    <td>
                        <a href=<?php
                        \hecho(SITE_BASE . $url); ?>>
                            <?php
                            \hecho(SITE_BASE . $url); ?>
                        </a>
                    </td>
                </tr>
                <?php
            endforeach; ?>
        </table>
        <?php
    }
}