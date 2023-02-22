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
        if (!$filepath) {
            echo "Aucune donnée n'a été renseignée";
        }

        if (!\is_readable($filepath)) {
            throw new UnrecoverableException("Aucune donnée n'a été renseignée");
        }

        $content = \json_decode(\file_get_contents($filepath), true, 512, \JSON_THROW_ON_ERROR);
        ?>
        <table class="table table-striped" aria-label="Définition de l'extraction">
            <?php
            foreach ($content as $element_id => $expression) : ?>
                <tr>
                    <th class="w500"><?php
                        \hecho($element_id); ?></th>
                    <td><?php
                        echo \nl2br(\get_hecho($expression)); ?></td>
                </tr>
                <?php
            endforeach; ?>
        </table>
        <?php
    }
}
