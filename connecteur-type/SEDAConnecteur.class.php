<?php

abstract class SEDAConnecteur extends Connecteur
{
    /**
     * Crée le bordereau en fonction des informations provenant du flux
     * @param FluxData $fluxData
     * @return string
     */
    abstract public function getBordereauNG(FluxData $fluxData): string;

    /**
     * Permet de valider un bordereau SEDA en fonction des schéma du connecteur
     * @param string $bordereau
     * @return bool
     */
    abstract public function validateBordereau(string $bordereau): bool;

    /**
     * Permet de récupérer les erreurs provenant de la validation du bordereau SEDA
     * @return LibXMLError[]
     */
    abstract public function getLastValidationError();

    /**
     *
     * Génère l'archive en fonction des données du flux sur archive_path
     * @param FluxData $fluxData
     * @param string $archive_path
     * @return void
     */
    abstract public function generateArchive(FluxData $fluxData, string $archive_path): void;

    /**
     * @param $file_path
     * @return array
     * @throws Exception
     */
    protected function getInfoARActes($file_path)
    {
        $file_name = basename($file_path);
        @ $xml = simplexml_load_file($file_path);
        if ($xml === false) {
            throw new Exception("Le fichier AR actes $file_name n'est pas exploitable");
        }
        $namespaces = $xml->getNameSpaces(true);
        if (empty($namespaces['actes'])) {
            throw new Exception("Le fichier AR actes $file_name n'est pas exploitable");
        }

        $attr = $xml->attributes($namespaces['actes']);
        if (!$attr) {
            throw new Exception("Le fichier AR actes $file_name n'est pas exploitable");
        }
        return ['DateReception' => $attr['DateReception'], 'IDActe' => $attr['IDActe']];
    }

    /**
     * @param FluxData $fluxData
     * @param string $archive_path
     * @param string $tmp_folder
     * @throws Exception
     */
    public function generateArchiveThrow(FluxData $fluxData, string $archive_path, string $tmp_folder): void
    {
        foreach ($fluxData->getFilelist() as $file_id) {
            $filename = $file_id['filename'];
            $filepath = $file_id['filepath'];

            if (!$filepath) {
                break;
            }
            $dirname = dirname($tmp_folder . "/" . $filename);
            if (!file_exists($dirname)) {
                mkdir($dirname, 0777, true);
            }
            copy($filepath, "$tmp_folder/$filename");
        }

        $command = "cd $tmp_folder && tar -cvzf $archive_path * 2>&1";

        exec($command, $output, $return_var);

        if ($return_var != 0) {
            $output = implode("\n", $output);
            throw new Exception(
                "Impossible de créer le fichier d'archive $archive_path - status : $return_var - output: $output"
            );
        }
    }
}
