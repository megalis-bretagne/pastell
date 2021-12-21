<?php

class TypologieActesVisionneuse extends Visionneuse
{
    public function display($filename, $filepath)
    {
        if (! file_exists($filepath)) {
            echo "La typologie n'a pas été choisie";
            return;
        }
        $filecontent = file_get_contents($filepath);

        $json = json_decode($filecontent, true);
        if (! $json) {
            echo "La typologie n'a pas pu être récupéré. Merci de la choisir à nouveau";
            return;
        }

        ?>
        <table style="border-style: solid; border-width: thin;">
            <tr>
                <th>Pièce</th>
                <th>Nom original du fichier</th>
                <th>Type de la pièce</th>
            </tr>
        <?php foreach ($json as $i => $line) : ?>
            <tr>
                <td><?php echo $i ? "Annexe numéro $i" : "Pièce principale" ?></td>
                <td><?php hecho($line['filename'] ?? "erreur") ?></td>
                <td><?php hecho($line['typologie'] ?? "erreur") ?></td>
            </tr>
        <?php endforeach; ?>
        </table>
        <?php
    }
}