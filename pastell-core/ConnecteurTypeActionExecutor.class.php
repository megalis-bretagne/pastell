<?php

abstract class ConnecteurTypeActionExecutor extends ActionExecutor
{
    protected $mapping;
    protected $data_seda_class_name;

    public function setMapping(array $mapping)
    {
        $this->mapping = $mapping;
    }

    public function getMappingValue($key)
    {
        if (empty($this->mapping[$key])) {
            return $key;
        }
        return $this->mapping[$key];
    }

    public function setDataSedaClassName($data_seda_class_name)
    {
        $this->data_seda_class_name = $data_seda_class_name;
    }

    public function getDataSedaClassName()
    {
        return $this->data_seda_class_name;
    }

    public function getDataSedaClassPath()
    {
        $extensions = $this->objectInstancier->getInstance("Extensions");

        foreach ($extensions->getAllModule() as $module_id => $module_path) {
            $fichier_recherche = $module_path . "/lib/" . $this->data_seda_class_name . ".class.php";
            if (file_exists($fichier_recherche)) {
                return $fichier_recherche;
            }
        }
        return false;
    }
}
