<?php

declare(strict_types=1);

namespace Pastell\Viewer;

final class GedIdDocumentsViewer implements Viewer
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
        <table style="border-style: solid; border-width: thin;" aria-label="Identifiants des documents sur la GED">
            <tr>
                <th>Nom du fichier</th>
                <th>Identifiant</th>
            </tr>
            <?php
            foreach ($json as $file => $gedId) : ?>
                <tr>
                    <td>
                        <?php
                        \hecho($file ?? 'ERREUR'); ?>
                    </td>
                    <td>
                        <?php
                        \hecho($gedId); ?>
                    </td>
                </tr>
                <?php
            endforeach; ?>
        </table>
        <?php
    }
}
