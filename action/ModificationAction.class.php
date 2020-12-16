<?php

class ModificationAction extends ActionExecutor
{

    public const ACTION_ID = "modification";

    /**
     * @return bool
     * @throws DonneesFormulaireException
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function go()
    {

        /** @var FileUploader $fileUploader */
        $fileUploader = $this->action_params['fileUploader'] ?? false;

        /** @var Recuperateur $recuperateur */
        $recuperateur = $this->action_params['recuperateur'] ?? false;
        $delete_file = $this->action_params['delete_file'] ?? false;
        $add_file = $this->action_params['add_file'] ?? false;

        if ($delete_file) {
            $field_name = $recuperateur->get('field');
            $field_num = $recuperateur->get('num', 0);
            $this->verifFieldIsEditable($this->id_e, $this->id_d, $field_name);
            $this->getDonneesFormulaire()->removeFile($field_name, $field_num);
        } elseif ($add_file) {
            $field_name = $recuperateur->get('field_name');
            $field_num = $recuperateur->get('field_num', 0);
            $file_name = $recuperateur->get('file_name');
            $file_path = $recuperateur->get('file_path');
            $this->verifFieldIsEditable($this->id_e, $this->id_d, $field_name);
            $this->getDonneesFormulaire()->addFileFromCopy($field_name, $file_name, $file_path, $field_num);
        } else {
            if (! $this->from_api) {
                $page = $recuperateur->get('page');
                $this->getDonneesFormulaire()->saveTab($recuperateur, $fileUploader, $page);
            } else {
                $this->getDonneesFormulaire()->setTabDataVerif($recuperateur->getAll());
                $this->getDonneesFormulaire()->saveAllFile($fileUploader);
            }
        }

        //Mise à jour du titre
        $titre_field = $this->getFormulaire()->getTitreField();
        $titre = $this->getDonneesFormulaire()->get($titre_field);
        if (is_array($titre)) {
            $titre = $titre[0] ?? $this->id_d;
        }
        $this->getDocument()->setTitre($this->id_d, $titre);

        if (
            $this->getDonneesFormulaire()->isModified()
            && $this->needChangeEtatToModification($this->id_e, $this->id_d, $this->getDocumentType())
        ) {
                $actionChange = $this->objectInstancier->getInstance(ActionChange::class);
                $actionChange->updateModification($this->id_d, $this->id_e, $this->id_u, self::ACTION_ID);
        }

        //Traitement du ONCHANGE
        $message = "";
        $result = true;

        foreach ($this->getDonneesFormulaire()->getOnChangeAction() as $action_on_change) {
            $actionExecutorFactory = $this->objectInstancier->getInstance(ActionExecutorFactory::class);
            $result = $result && $actionExecutorFactory->executeOnDocumentCritical(
                $this->id_e,
                $this->id_u,
                $this->id_d,
                $action_on_change,
                $this->id_destinataire,
                $this->from_api,
                $this->action_params,
                $this->id_worker
            );
            $last_message = $actionExecutorFactory->getLastMessage();
            if ($last_message) {
                $message .= "$last_message\n";
            }
        }
        if ($message) {
            $this->setLastMessage($message);
        }

        return $result;
    }

    public function needChangeEtatToModification($id_e, $id_d, DocumentType $documentType)
    {
        $action_name = $this->getDocumentActionEntite()->getLastAction($id_e, $id_d);

        $actionObject = $documentType->getAction();
        $modification_no_change_etat = $actionObject->getProperties($action_name, Action::MODIFICATION_NO_CHANGE_ETAT);

        return !$modification_no_change_etat;
    }

    /**
     * @param $id_e
     * @param $id_d
     * @return bool|mixed
     * @throws ForbiddenException
     */
    private function getEditableContent($id_e, $id_d)
    {
        //creation/modification => si editable_content, on prend editable_content, sinon, pas de editable_content (et tout est permis)
        //autre action => on prends editable_content, sinon, rien n'est editable (et on lance une erreur)
        $type_document = $this->getDocument()->getInfo($id_d)['type'];
        $last_action = $this->getDocumentActionEntite()->getLastActionNotModif($id_e, $id_d);
        $editable_content = $this->getDocumentTypeFactory()->getFluxDocumentType($type_document)->getAction()->getEditableContent($last_action);

        if ((! in_array($last_action, array("creation","modification"))) && ! $editable_content) {
            throw new ForbiddenException("Ce document n'est pas modifiable");
        }

        return $editable_content;
    }

    /**
     * @param $id_e
     * @param $id_d
     * @param $field_name
     * @throws ForbiddenException
     */
    private function verifFieldIsEditable($id_e, $id_d, $field_name)
    {
        $editable_content = $this->getEditableContent($id_e, $id_d);
        if (! $editable_content) {
            return;
        }
        if (! in_array($field_name, $editable_content)) {
            throw new ForbiddenException("Le contenu de $field_name n'est pas éditable");
        }
    }
}
