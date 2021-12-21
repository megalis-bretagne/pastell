<?php

class VersionAPIController extends BaseAPIController
{
    private $manifestFactory;

    public function __construct(ManifestFactory $manifestFactory)
    {
        $this->manifestFactory = $manifestFactory;
    }

    public function get()
    {
        $function = $this->getFromRequest('api_function');
        if (preg_match("#rest/allo#", $function)) {
            return $this->alloAction();
        }
        $info = $this->manifestFactory->getPastellManifest()->getInfo();
        $result = array();
        $result['version'] = $info['version'];
        $result['revision'] = $info['revision'];
        $result['last_changed_date'] = $info['last_changed_date'];
        $result['extensions_versions_accepted'] = $info['extensions_versions_accepted'];
        $result['version_complete'] = $info['version-complete'];
        return $result;
    }

    //Pour le logiciel ALLO. Cette fonction ne fait pas partie de l'API publique.
    public function alloAction()
    {
        $info = $this->manifestFactory->getPastellManifest()->getInfo();
        return array("produit" => "Pastell","version" => $info['version']);
    }
}
