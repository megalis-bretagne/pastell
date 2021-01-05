<?php

//Chargé des fichier entite-properties.yml et global-properties.yml

use Pastell\Service\Pack\PackService;

class ConnecteurDefinitionFiles
{

    public const NOM = 'nom';
    public const TYPE = 'type';
    public const DESCRIPTION = 'description';
    public const RESTRICTION_PACK = 'restriction_pack';

    public const ENTITE_PROPERTIES_FILENAME = "entite-properties.yml";
    public const GLOBAL_PROPERTIES_FILENAME = "global-properties.yml";

    private $extensions;
    private $yml_loader;
    private $packService;

    public function __construct(Extensions $extensions, YMLLoader $yml_loader, PackService $packService)
    {
        $this->extensions = $extensions;
        $this->yml_loader = $yml_loader;
        $this->packService = $packService;
    }

    public function getAll($global = false)
    {
        if ($global) {
            return $this->getAllGlobal();
        }
        return $this->getAllConnecteurByFile(self::ENTITE_PROPERTIES_FILENAME);
    }

    public function getAllGlobal()
    {
        return $this->getAllConnecteurByFile(self::GLOBAL_PROPERTIES_FILENAME);
    }

    private function getAllConnecteurByFile($file_name)
    {
        $result = array();
        foreach ($this->extensions->getAllConnecteur() as $id_connecteur => $connecteur_path) {
            $definition_file_path = $connecteur_path . "/" . $file_name;
            if (file_exists($definition_file_path)) {
                $connecteur_definition = $this->yml_loader->getArray($definition_file_path);
                if (!($connecteur_definition && $this->isRestrictedConnecteur($connecteur_definition))) {
                    $result[$id_connecteur] = $connecteur_definition;
                }
            }
        }
        uasort($result, array($this,"sortConnecteur"));
        return $result;
    }

    private function sortConnecteur($a, $b)
    {
        return strcasecmp($a[self::NOM], $b[self::NOM]);
    }


    public function getAllType()
    {
        return $this->getAllTypeByDef($this->getAll());
    }

    public function getAllGlobalType()
    {
        return $this->getAllTypeByDef($this->getAllGlobal());
    }

    private function getAllTypeByDef(array $connecteur_definition)
    {
        $result = array();
        foreach ($connecteur_definition as $def) {
            $result[$def[self::TYPE]] = 1;
        }
        $result = array_keys($result);

        usort($result, 'strcasecmp');
        return $result;
    }

    public function getAllByIdE($id_e)
    {
        return $id_e ? $this->getAll() : $this->getAllGlobal();
    }

    public function getInfo($id_connecteur, $global = false)
    {
        if ($global) {
            return $this->getInfoGlobal($id_connecteur);
        }
        $connecteur_path = $this->extensions->getConnecteurPath($id_connecteur);
        $array =  $this->yml_loader->getArray("$connecteur_path/" . self::ENTITE_PROPERTIES_FILENAME);

        if (isset($array['heritage'])) {
            $heritage_array = $this->yml_loader->getArray(PASTELL_PATH . "/common-yaml/{$array['heritage']}.yml");
            if ($heritage_array) {
                $array = array_merge_recursive($heritage_array, $array);
            }
        }
        return $array;
    }

    public function getInfoGlobal($id_connecteur)
    {
        $connecteur_path = $this->extensions->getConnecteurPath($id_connecteur);
        return $this->yml_loader->getArray("$connecteur_path/" . self::GLOBAL_PROPERTIES_FILENAME);
    }

    public function getConnecteurClass($id_connecteur)
    {
        $connecteur_path = $this->extensions->getConnecteurPath($id_connecteur);
        $all = glob("$connecteur_path/*.class.php");
        if (! $all) {
            throw new Exception("Impossible de trouver une classe pour le connecteur $id_connecteur");
        }
        $class_file = $all[0];
        $class_name = basename($class_file, ".class.php");
        if (!class_exists($class_name, false)) {
            require_once($class_file);
        }
        return $class_name;
    }

    public function getAllByFamille($famille_connecteur, $global = false)
    {
        $result = array();
        foreach ($this->getAll($global) as $connecteur_id => $connecteur_properties) {
            if ($connecteur_properties['type'] == $famille_connecteur) {
                $result[$connecteur_id] = true;
            }
        }
        $result = array_keys($result);
        usort($result, 'strcasecmp');
        return $result;
    }

    /**
     * @param bool $global
     * @return array
     */
    public function getAllRestricted(bool $global = false): array
    {
        if ($global) {
            return $this->getAllRestrictedGlobal();
        }
        return $this->getAllRestrictedByFile(self::ENTITE_PROPERTIES_FILENAME);
    }

    /**
     * @return array
     */
    private function getAllRestrictedGlobal(): array
    {
        return $this->getAllRestrictedByFile(self::GLOBAL_PROPERTIES_FILENAME);
    }

    /**
     * @param string $file_name
     * @return array
     */
    private function getAllRestrictedByFile(string $file_name): array
    {
        $result = array();
        foreach ($this->extensions->getAllConnecteur() as $id_connecteur => $connecteur_path) {
            $definition_file_path = $connecteur_path . "/" . $file_name;
            if (file_exists($definition_file_path)) {
                $connecteur_definition = $this->yml_loader->getArray($definition_file_path);
                if ($connecteur_definition && $this->isRestrictedConnecteur($connecteur_definition)) {
                    $result[] = $id_connecteur;
                }
            }
        }
        return $result;
    }

    /**
     * @param array $connecteur_definition
     * @return bool
     */
    private function isRestrictedConnecteur(array $connecteur_definition = []): bool
    {
        $restriction_pack = $connecteur_definition[self::RESTRICTION_PACK] ?? [];
        return (! $this->packService->hasOneOrMorePackEnabled($restriction_pack));
    }
}
