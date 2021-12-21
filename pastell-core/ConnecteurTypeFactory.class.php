<?php

class ConnecteurTypeFactory
{
    /** @var ObjectInstancier  */
    private $objectInstancier;

    public function __construct(ObjectInstancier $objectInstancier)
    {
        $this->objectInstancier = $objectInstancier;
    }

    /** @return Extensions */
    private function getExtensions()
    {
        return $this->objectInstancier->{'Extensions'};
    }

    public function getActionExecutor($connecteur_type_name, $action_class_name)
    {
        $connecteur_type_list = $this->getExtensions()->getAllConnecteurType();
        if (empty($connecteur_type_list[$connecteur_type_name])) {
            throw new RecoverableException("Impossible de trouver le connecteur type $connecteur_type_name");
        }

        $action_class_path = $connecteur_type_list[$connecteur_type_name] . "/" . $action_class_name . ".class.php";

        if (! file_exists($action_class_path)) {
            throw new RecoverableException("Le fichier $action_class_path n'a pas été trouvé");
        }

        require_once($action_class_path);

        /** @var ConnecteurTypeActionExecutor|ConnecteurTypeChoiceActionExecutor $action_class */
        $action_class = new $action_class_name($this->objectInstancier);

        return $action_class;
    }


    /**
     * @param $connecteur_type_name
     * @param $action_class_name
     * @return ConnecteurTypeChoiceActionExecutor
     * @throws RecoverableException
     */
    public function getChoiceActionExecutor($connecteur_type_name, $action_class_name)
    {
        /** @var ConnecteurTypeChoiceActionExecutor $action_class */
        $action_class = $this->getActionExecutor($connecteur_type_name, $action_class_name);
        return $action_class;
    }


    public function getAllActionExecutor()
    {
        $result = array();
        $connecteur_type_list = $this->getExtensions()->getAllConnecteurType();
        foreach ($connecteur_type_list as $connecteur_type_name => $connecteur_type_path) {
            foreach (glob("$connecteur_type_path/*.class.php") as $action_executor_path) {
                preg_match("#/([^/]+).class.php$#", $action_executor_path, $matches);
                $result[] = $matches[1];
            }
        }

        return $result;
    }
}
