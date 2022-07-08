<?php

declare(strict_types=1);

abstract class SEDAConnecteur extends Connecteur
{
    /**
     * Crée le bordereau en fonction des informations provenant du flux
     */
    abstract public function getBordereau(FluxData $fluxData): string;

    /**
     * Permet de valider un bordereau SEDA en fonction des schéma du connecteur
     */
    abstract public function validateBordereau(string $bordereau): bool;

    /**
     * Permet de récupérer les erreurs provenant de la validation du bordereau SEDA
     * @return LibXMLError[]
     */
    abstract public function getLastValidationError(): array;

    /**
     *
     * Génère l'archive en fonction des données du flux sur archive_path
     */
    abstract public function generateArchive(FluxData $fluxData, string $archive_path): void;

    public function generateArchiveThrow(FluxData $fluxData, string $archive_path, string $tmp_folder): void
    {
        foreach ($fluxData->getFilelist() as $file_id) {
            $filename = $file_id['filename'];
            $filepath = $file_id['filepath'];

            if (!$filepath) {
                break;
            }
            $dirname = \dirname($tmp_folder . '/' . $filename);
            if (!\file_exists($dirname) && !\mkdir($dirname, 0777, true) && !\is_dir($dirname)) {
                throw new \RuntimeException(\sprintf('Directory "%s" was not created', $dirname));
            }
            \copy($filepath, "$tmp_folder/$filename");
        }

        $command = "cd $tmp_folder && tar -cvzf $archive_path * 2>&1";

        \exec($command, $output, $return_var);

        if ($return_var !== 0) {
            $output = \implode("\n", $output);
            throw new \RuntimeException(
                "Impossible de créer le fichier d'archive $archive_path - status : $return_var - output: $output"
            );
        }
    }

    public function getTransferId(string $bordereau): string
    {
        $xml = \simplexml_load_string($bordereau);
        return (string)($xml->TransferIdentifier ?? $xml->MessageIdentifier);
    }
}
