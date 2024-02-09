<?php

use Symfony\Component\Console\Exception\MissingInputException;

class CFNEnvoyer extends ConnecteurTypeActionExecutor
{
    /**
     * @return bool
     * @throws Exception
     */
    public function go(): bool
    {
        $donneesFormulaire = $this->getDonneesFormulaire();
        $files = [];

        $zip = new ZipArchive();
        if ($zip->open($donneesFormulaire->getFilePath($this->getMappingValue('archive_bp'))) === true) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                /** @var string $fileName */
                $fileName = $zip->getNameIndex($i);
                $files[$fileName] = $zip->getFromIndex($i);
            }
            $zip->close();
        }

        /** @var CFNConnecteur $cfn */
        $cfn = $this->getConnecteur('CFN');
        $xml = $donneesFormulaire->getFileContent($this->getMappingValue('fichier_de_description'));
        if (!$xml) {
            throw new MissingInputException('Le fichier de description est vide');
        }
        $cfn->send($files, $xml, $donneesFormulaire);
        $message = sprintf(
            'Le dossier %s a été versé dans le coffre-fort numérique',
            $this->getDonneesFormulaire()->getTitre()
        );
        $this->addActionOK($message);
        $this->notify($this->action, $this->type, $message);
        return true;
    }
}
