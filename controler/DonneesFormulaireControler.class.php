<?php

class DonneesFormulaireControler extends PastellControler
{

    /**
     * @param $id_e
     * @param $id_d
     * @param $id_ce
     * @throws Exception
     */
    private function verifDroitOnDocumentOrConnecteur($id_e, $id_d, $id_ce)
    {
        if ($id_d) {
            $info = $this->getDocumentSQL()->getInfo($id_d);

            if (! $this->getDroitService()->hasDroit($this->getId_u(), $this->getDroitService()->getDroitEdition($info['type']), $id_e)) {
                if (! $this->isDocumentEmailChunkUpload()) {
                    echo "KO";
                    exit_wrapper();
                }
            }
        } elseif ($id_ce) {
            if (! $this->getRoleUtilisateur()->hasDroit($this->getId_u(), "entite:edition", $id_e)) {
                echo "KO";
                exit_wrapper();
            }
        } else {
            throw new Exception("id_d ou id_ce est obligatoire");
        }
    }

    /**
     * @throws Exception
     */
    public function downloadAllAction()
    {
        $getInfo = $this->getGetInfo();
        $id_e = $getInfo->getInt('id_e');
        $id_d = $getInfo->get('id_d');
        $id_ce = $getInfo->get('id_ce');
        $field = $getInfo->get('field');

        $this->verifDroitOnDocumentOrConnecteur($id_e, $id_d, $id_ce);
        $this->downloadAll($id_e, $id_d, $id_ce, $field);
    }

    /**
     * @throws Exception
     */
    public function downloadAll($id_e, $id_d, $id_ce, $field)
    {

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->getFromDocumentOrConnecteur($id_d, $id_ce);

        $zipArchive = new ZipArchive();
        $zip_filename = "/tmp/fichier-{$id_e}-" . ($id_d ?: $id_ce) . "-{$field}.zip";
        if (! $zipArchive->open($zip_filename, ZIPARCHIVE::CREATE)) {
            throw new Exception("Impossible de créer le fichier d'archive $zip_filename");
        }

        foreach ($donneesFormulaire->get($field) as $i => $fichier) {
            $file_path = $donneesFormulaire->getFilePath($field, $i);
            $file_name = $donneesFormulaire->getFileName($field, $i);
            if (! $zipArchive->addFile($file_path, $file_name)) {
                throw new Exception(
                    "Impossible d'ajouter le fichier $file_path ($file_name) dand l'archive $zip_filename"
                );
            }
        }
        $zipArchive->close();

        $sendFileToBrowser = $this->getObjectInstancier()->getInstance(SendFileToBrowser::class);
        $sendFileToBrowser->send($zip_filename);

        unlink($zip_filename);
    }


    private function isDocumentEmailChunkUpload()
    {
        /* mailsec ? */
        $key = $this->getPostOrGetInfo()->get('key');
        $documentEmail = $this->getObjectInstancier()->getInstance(DocumentEmail::class);
        $mailsec_info = $documentEmail->getInfoFromKey($key);
        if (! $mailsec_info) {
            return false;
        }
        $documentEmailReponseSQL = $this->getObjectInstancier()->getInstance(DocumentEmailReponseSQL::class);
        $id_d_reponse = $documentEmailReponseSQL->getDocumentReponseId($mailsec_info['id_de']);
        if ($this->getPostOrGetInfo()->get('id_d') != $id_d_reponse) {
            return false;
        }

        return true;
    }

    /**
     * @throws Exception
     */
    public function chunkUploadAction()
    {
        $id_e = $this->getPostOrGetInfo()->getInt('id_e');
        $id_d = $this->getPostOrGetInfo()->get('id_d');
        $id_ce = $this->getPostOrGetInfo()->get('id_ce');
        $field = $this->getPostOrGetInfo()->get('field');

        $this->verifDroitOnDocumentOrConnecteur($id_e, $id_d, $id_ce);

        $config = new \Flow\Config();
        $config->setTempDir(UPLOAD_CHUNK_DIRECTORY);

        $request = new \Flow\Request();

        $upload_filepath = UPLOAD_CHUNK_DIRECTORY . "/{$id_e}_{$id_d}_{$id_ce}_{$field}" . time() . "_" . mt_rand(0, mt_getrandmax());

        $this->getLogger()->debug("Chargement partiel du fichier : $upload_filepath dans (id_e={$id_e},id_d={$id_d},id_ce={$id_ce},field={$field}");

        if (\Flow\Basic::save($upload_filepath, $config, $request)) {
            $donneesFormulaire = $this->getDonneesFormulaireFactory()->getFromDocumentOrConnecteur($id_d, $id_ce);

            if ($donneesFormulaire->getFormulaire()->getField($field)->isMultiple()) {
                $nb_file = $donneesFormulaire->get($field) ? count($donneesFormulaire->get($field)) : 0;
                $this->getLogger()->debug("ajout fichier $nb_file");
                $donneesFormulaire->addFileFromCopy($field, $request->getFileName(), $upload_filepath, $nb_file);
            } else {
                $donneesFormulaire->addFileFromCopy($field, $request->getFileName(), $upload_filepath);
            }

            foreach ($donneesFormulaire->getOnChangeAction() as $action_on_change) {
                if ($id_ce) {
                    $result = $this->getActionExecutorFactory()->executeOnConnecteur($id_ce, $this->getId_u(), $action_on_change);
                } else {
                    $result = $this->getActionExecutorFactory()->executeOnDocument($id_e, $this->getId_u(), $id_d, $action_on_change);
                }
                if (!$result) {
                    $this->setLastError($this->getActionExecutorFactory()->getLastMessage());
                } elseif ($this->getActionExecutorFactory()->getLastMessage()) {
                    $this->setLastMessage($this->getActionExecutorFactory()->getLastMessage());
                }
            }
            $this->getLogger()->debug("chargement terminé");
            unlink($upload_filepath);
        }

        if (1 == mt_rand(1, 100)) {
            \Flow\Uploader::pruneChunks(UPLOAD_CHUNK_DIRECTORY);
        }
        echo "OK";
        exit_wrapper();
    }

    /**
     * @throws Exception
     */
    public function visionneuseAction()
    {
        $getInfo = $this->getGetInfo();
        $id_e = $getInfo->getInt('id_e');
        $id_d = $getInfo->get('id_d');
        $id_ce = $getInfo->get('id_ce');
        $field = $getInfo->get('field');
        $num = $getInfo->getInt('num');

        $this->verifDroitOnDocumentOrConnecteur($id_e, $id_d, $id_ce);

        try {
            $visionneuseFactory = $this->getObjectInstancier()->getInstance(VisionneuseFactory::class);
            if ($id_d) {
                $visionneuseFactory->display($id_d, $field, $num);
            } else {
                $visionneuseFactory->displayConnecteur($id_ce, $field, $num);
            }
        } catch (Exception $e) {
            echo "Une erreur est survenue : " . $e->getMessage();
        }
    }
}
