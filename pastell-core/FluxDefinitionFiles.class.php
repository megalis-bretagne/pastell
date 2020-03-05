<?php

//Chargement des fichier definition.yml dans les modules
class FluxDefinitionFiles
{
    
    public const DEFINITION_FILENAME = "definition.yml";

    public const PASTELL_ALL_FLUX_CACHE_KEY = "pastell_all_flux";

    private $extensions;
    private $yml_loader;
    private $memoryCache;
    private $cache_ttl_in_seconds;
    
    public function __construct(
        Extensions $extensions,
        YMLLoader $yml_loader,
        MemoryCache $memoryCache,
        $cache_ttl_in_seconds
    ) {
        $this->extensions = $extensions;
        $this->yml_loader = $yml_loader;
        $this->memoryCache = $memoryCache;
        $this->cache_ttl_in_seconds = $cache_ttl_in_seconds;
    }
    
    public function getAll()
    {
        $result = $this->memoryCache->fetch(self::PASTELL_ALL_FLUX_CACHE_KEY);
        if ($result) {
            return $result;
        }
        $result = array();
        $all_module = $this->extensions->getAllModule();
        foreach ($all_module as $module_path) {
            $file_config = $module_path . "/" . self::DEFINITION_FILENAME;
            $config = $this->yml_loader->getArray($file_config);
            $id_flux = basename(dirname($file_config));
            $result[$id_flux] = $config;
        }
        uasort($result, array($this,"compareFluxDefinition"));
        $this->memoryCache->store(
            self::PASTELL_ALL_FLUX_CACHE_KEY,
            $result,
            $this->cache_ttl_in_seconds
        );
        return $result;
    }

    private function compareFluxDefinition($a, $b)
    {
        $str1 = iconv('utf-8', 'ascii//TRANSLIT', $a[DocumentType::NOM]);
        $str2 = iconv('utf-8', 'ascii//TRANSLIT', $b[DocumentType::NOM]);

        return strcasecmp($str1, $str2);
    }
    
    public function getInfo($id_flux)
    {
        return $this->yml_loader->getArray($this->getDefinitionPath($id_flux));
    }

    public function getDefinitionPath($id_flux)
    {
        $module_path = $this->extensions->getModulePath($id_flux);
        return "$module_path/" . self::DEFINITION_FILENAME;
    }
}
