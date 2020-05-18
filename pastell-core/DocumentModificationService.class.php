<?php

class DocumentModificationService
{

    private $actionExecutorFactory;
    private $roleUtilisateur;
    private $documentSQL;
    private $actionPossible;


    public function __construct(
        ActionExecutorFactory $actionExecutorFactory,
        RoleUtilisateur $roleUtilisateur,
        DocumentSQL $documentSQL,
        ActionPossible $actionPossible
    ) {
        $this->actionExecutorFactory = $actionExecutorFactory;
        $this->roleUtilisateur = $roleUtilisateur;
        $this->documentSQL = $documentSQL;
        $this->actionPossible = $actionPossible;
    }

    /**
     * @param $id_e
     * @param $id_u
     * @param $id_d
     * @param Recuperateur $recuperateur
     * @param FileUploader $fileUploader
     * @return bool
     * @throws ForbiddenException
     */
    public function modifyDocumentWithoutAuthorizationChecking($id_e, $id_u, $id_d, Recuperateur $recuperateur, FileUploader $fileUploader, $from_api = false)
    {
        $result = $this->actionExecutorFactory->executeOnDocument(
            $id_e,
            $id_u,
            $id_d,
            ModificationAction::ACTION_ID,
            [],
            $from_api,
            [
                'recuperateur' => $recuperateur,
                'fileUploader' => $fileUploader,
            ]
        );

        if (! $result) {
            $lastException = $this->actionExecutorFactory->getLastException();
            if ($lastException) {
                throw $lastException;
            }
        }
        return $result;
    }

    /**
     * @param $id_e
     * @param $id_u
     * @param $id_d
     * @param Recuperateur $recuperateur
     * @param FileUploader $fileUploader
     * @param bool $from_api
     * @return bool
     * @throws ForbiddenException
     */
    public function modifyDocument($id_e, $id_u, $id_d, Recuperateur $recuperateur, FileUploader $fileUploader, $from_api = false)
    {
        $this->verifCanModify($id_e, $id_u, $id_d);
        return $this->modifyDocumentWithoutAuthorizationChecking(
            $id_e,
            $id_u,
            $id_d,
            $recuperateur,
            $fileUploader,
            $from_api
        );
    }

    public function addFile($id_e, $id_u, $id_d, $field_name, $field_num, $file_name, $file_path)
    {
        $result = $this->actionExecutorFactory->executeOnDocument(
            $id_e,
            $id_u,
            $id_d,
            ModificationAction::ACTION_ID,
            [],
            true,
            [
                'recuperateur' => new Recuperateur([
                    'field_name' => $field_name,
                    'field_num' => $field_num,
                    'file_name' => $file_name,
                    'file_path' => $file_path
                ]),
                'fileUploader' => new FileUploader(),
                'add_file' => true
            ]
        );
        if (! $result) {
            $lastException = $this->actionExecutorFactory->getLastException();
            if ($lastException) {
                throw $lastException;
            }
        }
        return $result;
    }

    /**
     * @param $id_e
     * @param $id_u
     * @param $id_d
     * @param $field_name
     * @param $field_num
     * @return bool
     * @throws ForbiddenException
     * @throws Exception
     */
    public function removeFile($id_e, $id_u, $id_d, $field_name, $field_num)
    {
        $this->verifCanModify($id_e, $id_u, $id_d);

        $result = $this->actionExecutorFactory->executeOnDocument(
            $id_e,
            $id_u,
            $id_d,
            ModificationAction::ACTION_ID,
            [],
            true,
            [
                'recuperateur' => new Recuperateur(['field' => $field_name,'num' => $field_num]),
                'fileUploader' => new FileUploader(),
                'delete_file' => true
            ]
        );
        if (! $result) {
            $lastException = $this->actionExecutorFactory->getLastException();
            if ($lastException) {
                throw $lastException;
            }
        }
        return $result;
    }

    /**
     * @param $id_e
     * @param $id_u
     * @param $id_d
     * @throws ForbiddenException
     * @throws Exception
     */
    private function verifCanModify($id_e, $id_u, $id_d)
    {
        $document_info = $this->documentSQL->getInfo($id_d);
        if (! $document_info) {
            throw new NotFoundException("Le document $id_d n'a pas été trouvé");
        }

        $droit = $this->roleUtilisateur->getDroitEdition($document_info['type']);
        if (! $this->roleUtilisateur->hasDroit($id_u, $droit, $id_e)) {
            throw new ForbiddenException("Acces interdit id_e=$id_e, droit=$droit,id_u=$id_u");
        }

        if (! $this->actionPossible->isActionPossible($id_e, $id_u, $id_d, 'modification')) {
            throw new ForbiddenException(
                "L'action « modification »  n'est pas permise : " . $this->actionPossible->getLastBadRule()
            );
        }
    }
}
