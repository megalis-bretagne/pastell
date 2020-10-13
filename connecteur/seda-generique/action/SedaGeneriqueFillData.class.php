<?php

require_once __DIR__ . "/../SedaGenerique.class.php";

class SedaGeneriqueFillData extends ChoiceActionExecutor
{
    /**
     * @return bool
     * @throws Exception
     */
    public function go()
    {
        $pastell_to_seda = SedaGenerique::getPastellToSeda();

        $json = [];

        foreach ($pastell_to_seda as $pastell_id => $element_info) {
            $json[$pastell_id] = $this->getRecuperateur()->get($pastell_id);
        }
        $json['keywords'] = $this->getRecuperateur()->get('keywords');
        $json['files'] = $this->getRecuperateur()->get('files');

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
        $all_used = $fluxEntiteSQL->getUsedByConnecteur($this->id_ce, null, $this->id_e);

        $flux = "";
        if (count($all_used) == 1) {
            $flux = $all_used[0]['flux'];
        }
        $documentType = $this->getDocumentTypeFactory()->getFluxDocumentType($flux);
        $this->{'fieldsList'} = ($documentType->getFormulaire()->getFieldsList());


        $this->{'flux'} = $flux;

        $json = $this->getConnecteurConfig($this->id_ce)->getFileContent('data');
        $this->{'data'} =  json_decode($json, true);

        $this->{'pastell_to_seda'} = SedaGenerique::getPastellToSeda();

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
