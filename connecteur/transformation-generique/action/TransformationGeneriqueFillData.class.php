<?php

require_once __DIR__ . "/../lib/TransformationGeneriqueDefinition.class.php";

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

        $data = [];
        foreach ($id_element_array as $i => $id_element) {
            $id_element = trim($id_element);
            if (! $id_element) {
                continue;
            }
            $data[$id_element] = $definition_array[$i] ?? "";
        }

        $transformationGeneriqueDefinition = $this->objectInstancier->getInstance(
            TransformationGeneriqueDefinition::class
        );

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
     */
    public function display()
    {
        $fluxEntiteSQL = $this->objectInstancier->getInstance(FluxEntiteSQL::class);
        $all_used = $fluxEntiteSQL->getUsedByConnecteur($this->id_ce, null, $this->id_e);

        $flux = "";
        if (count($all_used) == 1) {
            $flux = $all_used[0]['flux'];
        }
        $documentType = $this->getDocumentTypeFactory()->getFluxDocumentType($flux);
        $this->{'fieldsList'} = ($documentType->getFormulaire()->getFieldsList());
        $this->{'flux'} = $flux;

        $transformationGeneriqueDefinition = $this->objectInstancier->getInstance(
            TransformationGeneriqueDefinition::class
        );

        $transformation_data = $transformationGeneriqueDefinition->getData(
            $this->getConnecteurConfig($this->id_ce)
        );
        $transformation_data[''] = "";

        $this->{'transformation_data'} = $transformation_data;

        $this->renderPage(
            "Données de transformation",
            __DIR__ . "/../template/TransformationGeneriqueFillData.php"
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
