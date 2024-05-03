<?php

use Pastell\Validator\ElementIdValidator;

class TransformationGeneriqueFillData extends ChoiceActionExecutor
{
    /**
     * @return bool
     * @throws Exception
     */
    public function go()
    {
        $definition_array = $this->getRecuperateur()->get('definition');
        $id_element_array = $this->getRecuperateur()->get('id_element');

        $transformationGeneriqueDefinition = $this->objectInstancier->getInstance(
            TransformationGeneriqueDefinition::class
        );

        $data = [];
        foreach ($id_element_array as $i => $id_element) {
            $id_element = trim($id_element);
            if (! $id_element) {
                continue;
            }
            $elementIdValidator = new ElementIdValidator();
            $elementIdValidator->validate($id_element);
            $data[$id_element] = $definition_array[$i] ?? '';
        }

        $transformationGeneriqueDefinition->setTransformation(
            $this->getConnecteurConfig($this->id_ce),
            $data
        );

        if ($this->getRecuperateur()->get('add_button') === 'add') {
            $this->redirect("Connecteur/externalData?id_ce={$this->id_ce}&field={$this->field}");
            exit;
        }

        return true;
    }

    /**
     * @return bool
     * @throws NotFoundException
     */
    public function display()
    {
        $fluxEntiteSQL = $this->objectInstancier->getInstance(FluxEntiteSQL::class);
        $this->setViewParameter('flux', $fluxEntiteSQL->getUsedByConnecteurIfUnique($this->id_ce, $this->id_e));
        $documentType = $this->getDocumentTypeFactory()->getFluxDocumentType($this->getViewParameter()['flux']);
        $this->setViewParameter('fieldsList', $documentType->getFormulaire()->getFieldsList());

        $transformationGeneriqueDefinition = $this->objectInstancier->getInstance(
            TransformationGeneriqueDefinition::class
        );

        $transformation_data = $transformationGeneriqueDefinition->getData(
            $this->getConnecteurConfig($this->id_ce)
        );
        $transformation_data[''] = '';

        $this->setViewParameter('transformation_data', $transformation_data);

        $this->renderPage(
            'Données de transformation',
            'connector/transformation/TransformationGeneriqueFillData'
        );
        return true;
    }

    /**
     * @return bool
     */
    public function displayAPI()
    {
        return false;
    }
}
