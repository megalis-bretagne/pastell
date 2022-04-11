<?php

class SedaGeneriqueFillData extends ChoiceActionExecutor
{
    /**
     * @return bool
     * @throws Exception
     */
    public function go()
    {
        $pastell_to_seda = SedaGenerique::getPastellToSeda();

        $data = $this->getConnecteurConfig($this->id_ce)->getFileContent('data');
        $json = json_decode($data ?? "{}", true);

        foreach ($pastell_to_seda as $pastell_id => $element_info) {
            $json[$pastell_id] = $this->getRecuperateur()->get($pastell_id);
        }
        $json['keywords'] = $this->getRecuperateur()->get('keywords');

        $this->getConnecteurConfig($this->id_ce)->addFileFromData(
            'data',
            "data.json",
            json_encode($json)
        );

        return true;
    }

    public function display()
    {

        $fluxEntiteSQL = $this->objectInstancier->getInstance(FluxEntiteSQL::class);
        $this->setViewParameter('flux', $fluxEntiteSQL->getUsedByConnecteurIfUnique($this->id_ce, $this->id_e));
        $documentType = $this->getDocumentTypeFactory()->getFluxDocumentType($this->getViewParameter('flux'));
        $this->setViewParameter('fieldsList', $documentType->getFormulaire()->getFieldsList());

        $json = $this->getConnecteurConfig($this->id_ce)->getFileContent('data');
        $this->setViewParameter('data', json_decode($json, true));

        $this->setViewParameter('pastell_to_seda', SedaGenerique::getPastellToSeda());

        $this->renderPage(
            "Sélection des méta-données du bordereau",
            __DIR__ . "/../template/SedaGeneriqueFillData.php"
        );
        return true;
    }

    public function displayAPI()
    {
        // TODO: Implement displayAPI() method.
    }
}
