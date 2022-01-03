<?php

require_once __DIR__ . "/../SedaGenerique.class.php";

class SedaGeneriqueVisionneuse extends Visionneuse
{
    public function display($filename, $filepath)
    {
        if (! $filepath || ! file_exists($filepath)) {
            echo "<br/>Aucune donnée n'a été renseignée.<br/>";
            return false;
        }

        $pastell2Seda = SedaGenerique::getPastellToSeda();
        $content = json_decode(file_get_contents($filepath), true);
        ?>
        <table   class="table table-striped" >
            <?php foreach ($content as $key => $value) : ?>
                <tr>
                    <th class="w500"><?php hecho($pastell2Seda[$key]['libelle'] ?? $key); ?></th>
                    <td><?php echo nl2br(get_hecho($value)); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php
        return true;
    }
}
