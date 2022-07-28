<?php

class CreationAction extends ActionExecutor
{
    public const ACTION_ID = 'creation';

    /**
     * @throws Exception
     */
    public function go()
    {
        $this->getDocumentEntite()->addRole($this->id_d, $this->id_e, "editeur");
        $this->setDefaultValue();
        $this->addActionOK("Création du document");
        $this->notify($this->action, $this->type, "Création du document");
        return true;
    }

    private function setDefaultValue()
    {
        foreach ($this->getDonneesFormulaire()->getFormulaire()->getAllFields() as $field) {
            if ($field->getDefault()) {
                $this->getDonneesFormulaire()->setData($field->getName(), $field->getDefault());
                if ($field->getOnChange()) {
                    $actionExecutorFactory = $this->objectInstancier->getInstance(ActionExecutorFactory::class);
                    $actionExecutorFactory->executeOnDocumentCritical($this->id_e, $this->id_u, $this->id_d, $field->getOnChange());
                }
            }
        }
    }
}
