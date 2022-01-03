<?php

class ReponsePrefectureLinkVisionneuse extends Visionneuse
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
                <th>Type de réponse</th>
                <th>URL</th>
            </tr>
            <?php foreach ($json as $type_reponse => $url) : ?>
                <tr>

                    <td>
                        <?php hecho(TdtConnecteur::getTransactionNameFromNumber($type_reponse) ?? 'ERREUR') ?>
                    </td>
                    <td>
                        <a href=<?php hecho(SITE_BASE . $url); ?>>
                            <?php hecho(SITE_BASE . $url); ?>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php
    }
}