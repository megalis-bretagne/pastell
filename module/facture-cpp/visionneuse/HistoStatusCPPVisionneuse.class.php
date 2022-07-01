<?php

declare(strict_types=1);

use Pastell\Viewer\Viewer;

class HistoStatusCPPVisionneuse implements Viewer
{
    public function __construct(private FancyDate $fancyDate)
    {
    }

    /**
     * @throws \JsonException
     */
    public function display(string $filename, string $filepath): void
    {
        if (!\file_exists($filepath)) {
            throw new Exception("Aucun statut disponible");
        }
        $content = \file_get_contents($filepath);
        if (!$content) {
            throw new Exception("Impossible de lire le fichier");
        }
        $historique = \json_decode($content, true, 512, \JSON_THROW_ON_ERROR);
        if (!$historique) {
            throw new Exception("Le fichier $filename n'est pas dans le bon format (json)");
        }

        ?>
        <style>
            .histo_status_cpp {
                border-style: solid;
                border-width: thin;
                padding: 5px;
            }
        </style>
        <div class="histo_status_cpp">
            <p>
                Statut courant : <b><?php
                    \hecho($historique['statut_courant']); ?></b>
            </p>

            <table aria-label="Historique des statuts">
                <tr>
                    <th>Date de passage</th>
                    <th>Statut</th>
                    <th>Utilisateur</th>
                    <th>Commentaire</th>
                </tr>
                <?php
                foreach ($historique['histo_statut'] as $histo_statut) : ?>
                    <tr>
                        <td><?php
                            echo $this->fancyDate->getDateFr($histo_statut['statut_date_passage']) ?></td>
                        <td><?php
                            \hecho($histo_statut['statut_code']) ?></td>
                        <td><?php
                            \hecho(
                                $histo_statut['statut_utilisateur_prenom'] . " " . $histo_statut['statut_utilisateur_nom']
                            ) ?> </td>
                        <td><?php
                            \hecho($histo_statut['statut_commentaire']) ?></td>
                    </tr>
                    <?php
                endforeach; ?>

            </table>
        </div>
        <?php
    }
}
