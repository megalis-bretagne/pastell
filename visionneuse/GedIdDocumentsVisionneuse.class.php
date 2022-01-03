<?php

class GedIdDocumentsVisionneuse extends Visionneuse
{
    public function display($filename, $filepath)
    {
        if (!file_exists($filepath)) {
            echo "Le fichier n'existe pas";
            return;
        }
        $filecontent = file_get_contents($filepath);

        $json = json_decode($filecontent, true);
        if (!$json) {
            echo "Le fichier est vide";
            return;
        }
        ?>
        <table style="border-style: solid; border-width: thin;">
            <tr>
                <th>Nom du fichier</th>
                <th>Identifiant</th>
            </tr>
            <?php foreach ($json as $filename => $gedId) : ?>
                <tr>

                    <td>
                        <?php hecho($filename ?? 'ERREUR'); ?>
                    </td>
                    <td>
                        <?php hecho($gedId); ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php
    }
}